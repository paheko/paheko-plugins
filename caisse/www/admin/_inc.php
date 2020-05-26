<?php

namespace Garradin\Plugin\Caisse;

use Garradin\Utils;

function reload() {
	Utils::redirect(Utils::getSelfURL(true));
}

function get_amount(string $amount): int {
	$a = str_replace(',', '.', $amount);
	$a = preg_replace('/[^\d.]/', '', $a);
	$a = explode('.', $a);
	$a = sprintf('%d%02d', $a[0], @$a[1]);
	return $a;
}

function pos_amount(int $a): string {
	return sprintf("%d,%02d", (int) ($a/100), (int) ($a%100));
}

function pos_money(?int $a): string {
	return $a === null ? '' : pos_amount($a) . '&nbsp;€';
}

$tpl->register_modifier('pos_money', __NAMESPACE__ . '\\pos_money');
$tpl->register_modifier('pos_amount', __NAMESPACE__ . '\\pos_amount');
$tpl->register_modifier('image_base64', function (string $blob) {
	return 'data:image/png;base64,' . base64_encode($blob);
});

$tpl->assign('plugin_css', ['style.css']);
