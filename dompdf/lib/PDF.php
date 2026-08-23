<?php

namespace Paheko\Plugin\Dompdf;

use Paheko\Utils;

use Dompdf\Dompdf;
use const Paheko\{SHARED_CACHE_ROOT, CACHE_ROOT, WWW_URL, ADMIN_URL, PLUGINS_ROOT};

use Paheko\Entities\Signal;

class PDF
{
	const VERSION = '3.1.6';
	const URL = 'https://github.com/dompdf/dompdf/releases/download/v' . self::VERSION . '/dompdf-' . self::VERSION . '.zip';
	const DIRECTORY = SHARED_CACHE_ROOT . '/dompdf';
	const VERSION_FILE = self::DIRECTORY . '/dompdf/VERSION';
	const LOADER = self::DIRECTORY . '/dompdf/vendor/autoload.php';

	static public function remove(): void
	{
		Utils::deleteRecursive(self::DIRECTORY, true);
	}

	static public function install(): void
	{
		if (!function_exists('imagecreatefromwebp')) {
			throw new \LogicException('You need to install the PHP GD extension to be able to use this extension.');
		}

		// Remove old setup, new one is shared between setups
		if (file_exists(CACHE_ROOT . '/dompdf')) {
			Utils::deleteRecursive(CACHE_ROOT . '/dompdf', true);
		}

		if (file_exists(self::VERSION_FILE)) {
			$installed_version = trim(file_get_contents(self::VERSION_FILE));

			// Assume that a non-numeric version string is from Git
			if (!ctype_digit(substr($installed_version, 0, 1))) {
				return;
			}

			if ($installed_version === self::VERSION) {
				return;
			}

			self::remove();
		}

		$file = SHARED_CACHE_ROOT . '/dompdf.zip';

		if (ini_get('allow_url_fopen')) {
			copy(self::URL, $file);
		}
		elseif (!file_exists($file)) {
			echo 'Downloading files is forbidden by your server configuration (allow_url_fopen is disabled).<br />';
			printf('Please download this file: <a href="%s">%s</a><br />', self::URL);
			printf('And copy it here: <code>%s</code>', $file);
			exit;
		}

		// Make sure the installed version is overwritten by deleting the older one
		if (file_exists(self::DIRECTORY)) {
			Utils::deleteRecursive(self::RECURSIVE, true);
		}

		$zip = new \PharData($file);
		$zip->extractTo(self::DIRECTORY, null, true);
		unset($zip);

		Utils::safe_unlink($file);
	}

	static protected function DomPDF(): Dompdf
	{
		$file = self::LOADER;

		if (!file_exists($file)) {
			self::install();
		}

		require_once self::LOADER;

		// instantiate and use the dompdf class
		$dompdf = new Dompdf;
		$host1 = parse_url(WWW_URL, PHP_URL_HOST);
		$host2 = parse_url(ADMIN_URL, PHP_URL_HOST);

		$options = $dompdf->getOptions();

		// Set chroot for file:// protocol, just in case
		$options->setChroot(CACHE_ROOT);

		// Only alow http/https protocols
		$options->setAllowedProtocols(['http://', 'https://']);

		// see https://github.com/dompdf/dompdf/pull/3377
		if (method_exists($options, 'setAllowedRemoteHosts')) {
			$options->setAllowedRemoteHosts([$host1, $host2]);
		}

		// Allow remote requests
		$options->set('isRemoteEnabled', true);

		$options->set('defaultMediaType', 'print');
		$options->set('isJavascriptEnabled', false);

		return $dompdf;
	}

	static protected function render(string $html)
	{
		$dompdf = self::DomPDF();

		// Detect landscape output
		// see https://github.com/dompdf/dompdf/issues/3562
		if (strpos($html, 'data-prefer-landscape') !== false) {
			$dompdf->setPaper('A4', 'landscape');
		}

		try {
			$dompdf->loadHtml($html);

			// Render the HTML as PDF
			$dompdf->render();
		}
		catch (\Throwable $e) {
			file_put_contents(CACHE_ROOT . '/dompdf_error.html', $html);
			throw $e;
		}

		return $dompdf;
	}

	static public function create(Signal $signal): void
	{
		$html = file_get_contents($signal->getIn('source'));

		$dompdf = self::render($html);

		file_put_contents($signal->getIn('target'), $dompdf->output());
		$signal->stop();

	}

	static public function stream(Signal $signal): void
	{
		$dompdf = self::render($signal->getIn('string'));

		echo $dompdf->output();

		$signal->stop();
	}

	static public function facturx(Signal $signal): void
	{
		$xmp_path = PLUGINS_ROOT . '/invoice/factur-x/factur-x.xmp';

		// Somehow the invoice plugin is missing?! Weird
		if (!file_exists($xmp_path)) {
			throw new \LogicException('factur-x.xmp file is missing');
		}

		$dompdf = self::DomPDF();
		$dompdf->get_canvas()->get_cpdf()->defaultFont = "./lib/fonts/DejaVuSerif.ttf";

		$options = $dompdf->getOptions();

		// https://github.com/dompdf/dompdf/wiki/PDFA-Support
		$options->set('isPdfAEnabled', true);

		$font_metrics = $dompdf->getFontMetrics();
		$font_metrics->setFontFamily("courier", $font_metrics->getFamily("DejaVu Sans Mono"));
		$font_metrics->setFontFamily("fixed", $font_metrics->getFamily("DejaVu Sans Mono"));
		$font_metrics->setFontFamily("helvetica", $font_metrics->getFamily("DejaVu Sans"));
		$font_metrics->setFontFamily("monospace", $font_metrics->getFamily("DejaVu Sans Mono"));
		$font_metrics->setFontFamily("sans-serif", $font_metrics->getFamily("DejaVu Sans"));
		$font_metrics->setFontFamily("serif", $font_metrics->getFamily("DejaVu Serif"));
		$font_metrics->setFontFamily("times", $font_metrics->getFamily("DejaVu Serif"));
		$font_metrics->setFontFamily("times-roman", $font_metrics->getFamily("DejaVu Serif"));

		$tmp_xml_path = tempnam(CACHE_ROOT, 'facturx-xml-dompdf-');
		file_put_contents($tmp_xml_path, $signal->getIn('xml'));

		try {
			$dompdf->loadHtml($signal->getIn('html'));

			// Render the HTML as PDF
			$dompdf->render();
		}
		catch (\Throwable $e) {
			file_put_contents(CACHE_ROOT . '/dompdf_error.html', $html);
			throw $e;
		}

		$xmp = <<<EOF
		<!-- The actual Factur-X properties; adjust if required -->
		<rdf:Description rdf:about="" xmlns:fx="urn:factur-x:pdfa:CrossIndustryDocument:invoice:1p0#">
			<fx:DocumentType>INVOICE</fx:DocumentType>
			<fx:DocumentFileName>factur-x.xml</fx:DocumentFileName>
			<fx:Version>1.0</fx:Version>
			<fx:ConformanceLevel>BASIC</fx:ConformanceLevel>
		</rdf:Description>

		<!-- PDF/A extension schema description for the Factur-X schema.
		It is crucial for PDF/A-3 conformance. Don't touch! -->
		<rdf:Description rdf:about=""
			xmlns:pdfaExtension="http://www.aiim.org/pdfa/ns/extension/"
			xmlns:pdfaSchema="http://www.aiim.org/pdfa/ns/schema#"
			xmlns:pdfaProperty="http://www.aiim.org/pdfa/ns/property#">
			<pdfaExtension:schemas>
				<rdf:Bag>
					<rdf:li rdf:parseType="Resource">
						<pdfaSchema:schema>Factur-X PDFA Extension Schema</pdfaSchema:schema>
						<pdfaSchema:namespaceURI>urn:factur-x:pdfa:CrossIndustryDocument:invoice:1p0#</pdfaSchema:namespaceURI>
						<pdfaSchema:prefix>fx</pdfaSchema:prefix>
						<pdfaSchema:property>
						<rdf:Seq>
							<rdf:li rdf:parseType="Resource">
								<pdfaProperty:name>DocumentFileName</pdfaProperty:name>
								<pdfaProperty:valueType>Text</pdfaProperty:valueType>
								<pdfaProperty:category>external</pdfaProperty:category>
								<pdfaProperty:description>name of the embedded XML invoice file</pdfaProperty:description>
							</rdf:li>
							<rdf:li rdf:parseType="Resource">
								<pdfaProperty:name>DocumentType</pdfaProperty:name>
								<pdfaProperty:valueType>Text</pdfaProperty:valueType>
								<pdfaProperty:category>external</pdfaProperty:category>
								<pdfaProperty:description>INVOICE</pdfaProperty:description>
							</rdf:li>
							<rdf:li rdf:parseType="Resource">
								<pdfaProperty:name>Version</pdfaProperty:name>
								<pdfaProperty:valueType>Text</pdfaProperty:valueType>
								<pdfaProperty:category>external</pdfaProperty:category>
								<pdfaProperty:description>The actual version of the Factur-X XML schema</pdfaProperty:description>
							</rdf:li>
							<rdf:li rdf:parseType="Resource">
								<pdfaProperty:name>ConformanceLevel</pdfaProperty:name>
								<pdfaProperty:valueType>Text</pdfaProperty:valueType>
								<pdfaProperty:category>external</pdfaProperty:category>
								<pdfaProperty:description>The conformance level of the embedded Factur-X data</pdfaProperty:description>
							</rdf:li>
						</rdf:Seq>
						</pdfaSchema:property>
					</rdf:li>
				</rdf:Bag>
			</pdfaExtension:schemas>
		</rdf:Description>
EOF;

		$cpdf = $dompdf->getCanvas()->get_cpdf();
		$cpdf->setAdditionalXmpRdf($xmp);
		$cpdf->addEmbeddedFile($tmp_xml_path, 'factur-x.xml', 'Invoice (Factur-X)', 'text/xml', [$cpdf->catalogId => 'Data']);

		$signal->setOut('pdf_string', $dompdf->output());
		$signal->stop();
	}
}
