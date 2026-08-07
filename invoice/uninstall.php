<?php

namespace Paheko\Plugin\Invoice;

use Paheko\DB;

$db = DB::getInstance();

$db->exec('
	DROP TABLE plugin_invoice_lines;
	DROP TABLE plugin_invoice_payments;
	DROP TABLE plugin_invoice_invoices;
	DROP TABLE plugin_invoice_clients;
');