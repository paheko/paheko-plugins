<?php

class AFNOR
{
	/**
	 * @see https://www.superpdp.tech/openapi/#afnor-flow/tag/flow/POST/v1/flows
	 */
	public function sendInvoice(Provider $provider, Invoice $invoice): void
	{
		$export = $invoice->exportAs('ubl');
		$http = new HTTP;
		$headers = $provider->getAuthHeaders();
		$data = [
			'file' => [
				'type' => 'text/xml',
				'name' => $invoice->getFilename('ubl'),
				'body' => $export,
			],
			'flowInfo' => [
				'flowSyntax' => 'UBL',
				'name' => $invoice->getFilename('ubl'),
				'flowProfile' => 'Basic',
				'processingRule' => 'B2B',
				'sha256' => hash('sha256', $export),
				'trackingId' => $invoice->id,
			],
		];

		$r = $http->POST($provider->url_prefix . '/flows', $data, 'multipart/form-data', $headers);

		if ($r->status !== 202) {
			throw new \LogicException('API error: ' . $r->status . "\n" . $r->body);
		}

		$r = json_decode($r->body);

		Plugins::fire('invoice.sent', ['id' => $r->flowId, 'provider' => $provider->name]);

		$invoice->setSubmitted($provider->name, $r->flowId, new DateTime($r->submittedAt));
	}

	/**
	 * @see https://www.superpdp.tech/openapi/#afnor-flow/tag/flow/POST/v1/flows/search
	 */
	public function updateInvoiceDetails(Provider $provider)
	{
		if (!$invoice) {
			// Log received invoice that wasn't seen before
			Plugins::fire('invoice.received', ['id' => $r->flowId, 'provider' => $provider->name]);
		}
	}
}