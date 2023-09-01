<?php

namespace Paheko;

use Paheko\Files\Files;
use Paheko\Entities\Files\File;
use Paheko\UserTemplate\UserTemplate;

use Paheko\Plugin\Caisse\Tabs;

require __DIR__ . '/_inc.php';

$tab = Tabs::get(qg('id'));

if ('' === trim($tab->name)) {
	throw new UserException('La note n\'a pas de nom associé : impossible de produire la facture');
}

$printer = shell_exec('which prince') ? 'prince' : (shell_exec('which chromium') ? 'chromium' : null);

if (!$printer) {
	die('Impossible de trouver Prince XML ou Chrome');
}

$items = $tab->listItems();
$payments = $tab->listPayments();
$remainder = $tab->getRemainder();
$options = $tab->listPaymentOptions();

$eligible = 0;

foreach ($options as $k => &$option) {
	if ($option->id != 3) {
		unset($options[$k]);
		continue;
	}

	$eligible = $option->amount;
}

$remainder_after = $remainder - $eligible;

$file = Files::get(File::CONTEXT_MODULES . '/web/caisse_invoice.html');

$tpl = new UserTemplate($file);

if (!$file) {
	$tpl->setSource(PLUGIN_ROOT . '/templates/invoice.skel');
}

$tpl->registerSection('items', function () use ($items) {
	foreach ($items as $item) {
		yield (array) $item;
	}
});

$tpl->registerSection('payments', function () use ($payments) {
	foreach ($payments as $item) {
		yield (array) $item;
	}
});

$tpl->assignArray(compact('tab', 'remainder', 'eligible', 'remainder_after'));

$result = $tpl->fetch();
$file_name = sprintf('Reçu %06d - %s.pdf', $tab->id, preg_replace('/[^\w]+/Ui', ' ', $tab->name));

header('Content-type: application/pdf');
header(sprintf('Content-Disposition: attachment; filename="%s"', $file_name));

Utils::streamPDF($result);
