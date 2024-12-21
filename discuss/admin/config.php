<?php

namespace Paheko\Plugin\Discuss;

require __DIR__ . '/../_inc.php';

$config = GList::getInstance()->config;

if (!empty($_POST['save']))
{
	foreach ($config->all() as $key => $_v) {
		$value = $_POST[$key] ?? null;

		if ($key == 'max_attachment_size') {
			$value = (int) (((float) $value) * 1024 * 1024);
		}

		$config->$key = $value;
	}
}

$types = array_keys(Config::ALLOWED_ATTACHMENT_TYPES);
$types = array_map('strtoupper', $types);
$types = implode(', ', $types);

$data = $config->all();

if ($data['max_attachment_size'] > 0) {
	$data['max_attachment_size'] = round($data['max_attachment_size'] / (1024 * 1024), 1);
}

Template::display('admin/config.tpl', ['config' => $data, 'types' => $types]);
