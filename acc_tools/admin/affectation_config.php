<?php

namespace Paheko;

$rules = $plugin->getConfig('rules') ?? [
	[
		'match' => 'Versement espèces',
		'only_if' => 'positive',
		'debit' => '512A',
		'credit' => '530',
	],
	[
		'match' => 'Frais bancaires Crédit Mutuel',
		'only_if' => 'negative',
		'debit' => '627',
		'credit' => '512A',
	],
	[
		'match' => 'VIR LEMONWAY SAS',
		'only_if' => 'positive',
		'debit' => '512A',
		'credit' => '754',
	],
	[
		'match' => 'PRLV SEPA OVH SAS',
		'only_if' => 'negative',
		'debit' => '626',
		'credit' => '512A',
	],
	[
		'match' => 'VIR PAYPAL EUROPE S.A.R.L.',
		'only_if' => 'positive',
		'debit' => '512A',
		'credit' => '512C',
	],
	[
		'match' => 'Commission PayPal sur transactions',
		'only_if' => 'negaive',
		'debit' => '627',
		'credit' => '512C',
	],
];

$csrf_key = 'acc_tools_affect_config';

$form->runIf('save', function () use ($plugin) {
	$plugin->setConfigProperty('rules', Utils::array_transpose(f('rules')));
	$plugin->save();
}, $csrf_key, Utils::getSelfURI());

$only_options = ['negative' => 'négative (< 0)', 'positive' => 'positive (> 0)'];

$tpl->assign(compact('rules', 'csrf_key', 'only_options'));

$tpl->display(PLUGIN_ROOT . '/templates/affectation_config.tpl');
