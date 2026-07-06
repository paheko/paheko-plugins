<?php

namespace Paheko\Plugin\Invoice;

use Paheko\Template;

use Paheko\Plugin\Invoice\Entities\Client;
use Paheko\Plugin\Invoice\Entities\Line;

$tpl = Template::getInstance();

$tpl->register_modifier('get_unit_label', fn($code) => Line::UNITS[$code]);
$tpl->register_modifier('format_vat_rate', fn($rate) => str_replace('.', ',', $rate) . ' %');
$tpl->register_modifier('get_contact_scheme_label', fn($scheme) => Client::SCHEMES[$scheme] ?? 'Numéro');
