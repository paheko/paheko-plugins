<?php

namespace Paheko\Plugin\Invoice\Entities;

use Paheko\Entity;
use Paheko\Exec;
use Paheko\Plugins;
use Paheko\Static_Cache;
use Paheko\Template;
use Paheko\Utils;

use stdClass;

use const Paheko\{STATIC_CACHE_ROOT, ADMIN_COLOR1, ADMIN_COLOR2};

abstract class AbstractInvoice extends Entity
{
	const TYPE_QUOTE = 231;
	const TYPE_INVOICE = 380;
	const TYPE_CREDIT = 381;
	//const TYPE_CORRECTION = 384;
	const TYPE_SELF_BILLING = 389;

	/**
	 * Factur-X (BT-3) only allows some codes, not all of them!
	 * @see https://service.unece.org/trade/untdid/d99a/uncl/uncl1001.htm
	 * @see https://api.agicap.com/guides/einvoicing
	 */
	const TYPES = [
		self::TYPE_QUOTE => 'Devis',
		self::TYPE_INVOICE => 'Facture',
		self::TYPE_CREDIT => 'Avoir', // Avoir : quand la facture d'origine a déjà été payée
		self::TYPE_SELF_BILLING => 'Auto-facturation',
		//self::TYPE_CORRECTION => 'Facture rectificative', // rectificative : quand la facture d'origine n'a pas été payée ET qu'on ne modifie aucun montant
		//386 => 'Facture d\'acompte',
	];

	abstract public function isSellerOrg(): bool;
	abstract public function isQuote(): bool;
	abstract public function isDraft(): bool;
	abstract public function getExport(): stdClass;
	abstract public function getReference(): ?string;

	public function exportAs(string $format, ?string $parent_format = null): string
	{
		if ($format === 'facturx') {
			$xml = $this->exportAs('cii', $format);
			$html = $this->exportAs('html', $format);
			return $this->createFacturX($xml, $html);
		}

		$template = match ($format) {
			'cii' => 'cii.xml',
			'ubl' => 'ubl.xml',
			'html' => 'print.html',
		};

		$tpl = Template::getInstance();

		if ($format === 'html') {
			$tpl->assign('is_received', $this instanceof ReceivedInvoice);
			$tpl->assign('is_org', $this->isSellerOrg());
			$tpl->assign('is_draft', $this->isDraft());
			$tpl->assign('status', $this->status);
			$tpl->assign('is_quote', $this->isQuote());
			$tpl->assign(compact('parent_format'));
			$tpl->assign('color1', $config->color1 ?: ADMIN_COLOR1);
			$tpl->assign('color2', $config->color2 ?: ADMIN_COLOR2);

			if (isset($_GET['print'])) {
				$tpl->assign('facturx_enabled', $this->canExportAsFacturX());
			}
			else {
				$tpl->assign('css', file_get_contents(__DIR__ . '/../../admin/invoice.css'));
				$tpl->assign('export', true);
			}
		}

		$tpl->assign('invoice', $this->getExport());

		if ($format === 'cii') {
			$tpl->setEscapeType('xml');
		}

		$out = $tpl->fetch(__DIR__ . '/../../templates/invoice/' . $template);

		if ($format === 'cii') {
			// [PEPPOL-EN16931-R008]-Document MUST not contain empty elements. (still status warning)
			$out = preg_replace('!<(.*)>\s*</\\1>!', '', $out);

			// Remove comments
			$out = preg_replace('/<!--.*?-->/s', '', $out);
		}

		return $out;
	}

	public function streamAs(string $format, bool $download = false): void
	{
		$mimetype = match ($format) {
			'facturx' => 'application/pdf',
			'html'    => 'text/html',
			default   => 'text/xml',
		};

		header('Content-Type: ' . $mimetype);

		header(sprintf('Content-Disposition: %s; filename="%s"', $download ? 'attachment' : 'inline', $this->getFilename($format)));

		echo $this->exportAs($format);
	}

	public function downloadAs(string $format): void
	{
		$this->streamAs($format, true);
	}

	public function getFilename(string $format): string
	{
		$extension = match($format) {
			'facturx' => 'pdf',
			'html'    => 'html',
			default   => 'xml',
		};

		return ($this->getReference() ?? 'Brouillon') . '.' . $extension;
	}

	/**
	 * @see https://www.ghostscript.com/blog/zugferd.html
	 */
	protected function createFacturX(string $xml, string $html): string
	{
		$signal = Plugins::fire('facturx.create', true, ['html' => $html, 'xml' => $xml], ['pdf_string' => null]);

		if ($signal && $signal->isStopped()) {
			if ($str = $signal->getOut('pdf_string')) {
				return $str;
			}
			else {
				throw new \LogicException('Signal facturx.create did not return a string');
			}
		}

		$id = 'facturx_' . sha1(random_bytes(10));
		$tmp_xml_dir = STATIC_CACHE_ROOT . '/' . $id;

		// the file MUST be called factur-x.xml, if not Prince will use its name in the PDF
		// and the PDF won't be valid (attached XML file must be named factur-x.xml)
		$tmp_xml_file = $tmp_xml_dir . '/factur-x.xml';
		$root = realpath(__DIR__ . '/../..');
		$xmp_path = $root . '/factur-x/factur-x.xmp';

		// We can't use Static_Cache class as the file MUST be called "factur-x.xml"
		// or it won't work!
		Utils::safe_mkdir($tmp_xml_dir, null, true);

		file_put_contents($tmp_xml_file, $xml);

		$cmd = Utils::getPDFCommand();
		$exec = new Exec;
		$exec->addBind($xmp_path);

		// Prince can directly create a valid Factur-X PDF using STDIN/STDOUT,
		// without temporary files for HTML and PDF, much better
		if (strpos($cmd, 'prince') === 0) {
			$cmd = Utils::getPrinceCommand('PDF/A-3a');
			$exec->setCommand($cmd);
			$exec->addParams([
				//'--fail-pdf-profile-error',
				//'--fail-pdf-tag-error',
				'--fail-missing-resources',
				'--fail-dropped-content',
				sprintf('--pdf-xmp=%s', escapeshellarg($xmp_path)),
				sprintf('--attach-data=%s',escapeshellarg($tmp_xml_file)),
				'-o - -',
			]);
		}
		// Weasyprint can also do it: https://github.com/Kozea/WeasyPrint/pull/2658
		elseif (strpos($cmd, 'weasyprint') === 0) {
			$exec->setCommand($cmd);
			$exec->addParams([
				'- -', // read from STDIN, write to STDOUT
				sprintf('--attachment=%s', escapeshellarg($tmp_xml_file)),
				'--attachment-relationship=Data',
				sprintf('--xmp-metadata=%s', escapeshellarg($xmp_path)),
				'--pdf-variant=pdf/a-3a',
			]);
		}

		try {
			if ($exec->hasCommand()) {
				$exec->setStdin($html);
				if ($exec->run() && null === $exec->getStdout() && !empty($exec->getStderr())) {
					throw new \RuntimeException(sprintf("Error running PDF command: %s\n%s", $exec->getCommand(), $exec->getStderr()));
				}

				return $exec->getStdout();
			}

			if (!Exec::quick('which gs', 1)) {
				throw new \LogicException('Cannot create Factur-X file: ghostscript is not installed');
			}

			// If Prince is not available, use ghostscript
			$tmp_pdf_file = Utils::filePDF($html);

			$cmd = sprintf('gs --permit-file-read=%s'
				. ' -sDEVICE=pdfwrite'
				. ' -dPDFA=3'
				. ' -sColorConversionStrategy=RGB'
				. ' -sZUGFeRDXMLFile=%s'
				. ' -sZUGFeRDProfile=%s'
				. ' -sZUGFeRDVersion=2p1'
				. ' -sZUGFeRDConformanceLevel=MINIMUM'
				. ' -dPDFACompatibilityPolicy=1'
				. ' -o %s %s %s',
				escapeshellarg($root . ':' . STATIC_CACHE_ROOT),
				escapeshellarg($tmp_xml_file),
				escapeshellarg($root . '/factur-x/rgb.icc'),
				escapeshellarg($path ?? '-'),
				escapeshellarg($root . '/factur-x/zugferd.ps'),
				escapeshellarg($tmp_pdf_file)
			);

			return Exec::quick($cmd, 5);
		}
		finally {
			if (isset($tmp_pdf_file)) {
				Utils::safe_unlink($tmp_pdf_file);
			}

			Utils::safe_unlink($tmp_xml_file);
			@rmdir($tmp_xml_dir);
		}
	}

	public function canExportAsFacturX(): bool
	{
		if (Plugins::hasSignal('facturx.create')) {
			return true;
		}

		$cmd = Utils::getPDFCommand();

		if (0 === strpos($cmd, 'prince')) {
			return true;
		}
		elseif (0 === strpos($cmd, 'weasyprint')) {
			return true;
		}

		return (bool) Exec::quick('which gs', 1);
	}
}
