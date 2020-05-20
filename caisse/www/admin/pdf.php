<?php

namespace Garradin;

use Garradin\Plugin\Caisse\Tab;

require __DIR__ . '/_inc.php';

$tab = new Tab(qg('id'));

$tabs = Tab::listForSession($pos_session->id);
$current_tab = $tabs[qg('id')];

if ('' === trim($current_tab->name)) {
	throw new UserException('La note n\'a pas de nom associé : impossible de produire la facture');
}

if (!shell_exec('which chromium')) {
	die('Impossible de trouver Chromium');
}

$tpl->assign('tab', $current_tab);
$tpl->assign('items', $tab->listItems());
$tpl->assign('existing_payments', $tab->listPayments());
$remainder = $tab->getRemainder();
$options = $tab->listPaymentOptions();

foreach ($options as $k => &$option) {
	if ($option->id != 3) {
		unset($options[$k]);
		continue;
	}

	$eligible = $option->amount;
}

if (empty($eligible)) {
	throw new UserException('Rien n\'est éligible à Coup de pouce vélo');
}

$remainder_after = $remainder - $eligible;

$tpl->assign('remainder', $remainder);
$tpl->assign('remainder_after', $remainder_after);
$tpl->assign('payment_options', $options);

$tpl->register_modifier('show_methods', function ($m) {
	$m = explode(',', $m);
	if (in_array(3, $m)) {
		return '<i>Oui</i>';
	}
});

$result = $tpl->fetch(PLUGIN_ROOT . '/templates/invoice.tpl');

//echo $result; exit;

$descriptorspec = array(
   0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
   1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
   2 => array('pipe', 'w'),
);

$cmd = 'prince -o - -';
$process = proc_open($cmd, $descriptorspec, $pipes);

if (is_resource($process)) {
    // $pipes now looks like this:
    // 0 => writeable handle connected to child stdin
    // 1 => readable handle connected to child stdout

    fwrite($pipes[0], $result);
    fclose($pipes[0]);

    $pdf_content = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    // It is important that you close any pipes before calling
    // proc_close in order to avoid a deadlock
    $return_value = proc_close($process);

    header('Content-type: application/pdf');
    //header(sprintf('Content-Length: %d', strlen($pdf_content)));
    header(sprintf('Content-Disposition: attachment; filename="Facture - %d.pdf"', qg('id')));
    echo $pdf_content;
}

