<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<meta charset="utf-8" />
	<title>Facture</title>
	<style type="text/css">
	{literal}
	@page {
		size: A4;
		margin: 1.5cm;
		@bottom {
			content: "Page " counter(page) " / " counter(pages);
			font-size: 8pt;
			margin-bottom: 10mm;
			text-align: center;
		}

	}
	* { margin: 0; padding: 0; }
	body {
		font-family: Arial, Helvetica, sans-serif;
		background: #fff;
		color: #000;
		font-size: 10pt;
	}
	header {
		display: flex;
		align-items: center;
		justify-content: center;
	}
	header img {
		width: 8em;
	}
	header div {
		text-align: center;
		margin: 1em;
	}
	h1 {
		font-size: 1.2rem;
	}
	h2 {
		font-size: 1rem;
		font-weight: normal;
	}
	h3 {
		font-size: .8rem;
		font-weight: normal;
	}
	.details {
		margin: 1rem 0;
		text-align: center;
	}

	table {
		margin: 1rem auto;
		border-collapse: collapse;
		width: 100%;
	}
	table tr {
		border: .1rem solid #000;
	}

	table th, table td {
		padding: .3rem .6rem;
		text-align: left;
		border-right: .1rem dotted #999;
	}

	table thead {
		background: #000;
		color: #fff;
	}
	table tr.foot {
		background: #eee;
		font-size: 12pt;
	}
	{/literal}
	</style>
</head>

<body>

<header>
	<img src="data:image/png;base64,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
" />
	<div>
		<h1>La rustine — atelier associatif de réparation de vélos</h1>
		<h2>Association « loi 1901 » à but non lucratif — SIRET 538 625 773 00022</h2>
		<h3>5 rue du Havre, 21000 DIJON — 03 73 27 03 66 — contact@larustine.org — http://larustine.org/</h3>
	</div>
</header>

<section class="details">
	<h1>Facture n°{"CDP-%04d"|args:$tab.id}</h1>
	<h2>Entretien vélo dans le cadre du "Coup de pouce Vélo - Réparation"</h2>
	<h3>Adhérent : {$tab.name}</h3>
	<h4>Date de la facture : {$tab.opened|date_format:"%d/%m/%Y"} — Date d'échéance : {$tab.opened|date_format:"%d/%m/%Y"}</h4>
</section>

<section class="items">
	<form method="post">
	<table class="list">
		<thead>
			<th>Dénomination</th>
			<td>Éligible Coup de pouce vélo</td>
			<td>Qté</td>
			<td>Prix</td>
			<td>Total</td>
		</thead>
		<tbody>
		{foreach from=$items item="item"}
			<tr>
				<th>{$item.name}</th>
				<td>{$item.methods|raw|show_methods}</td>
				<td>{$item.qty}</td>
				<td>{$item.price|raw|pos_money}</td>
				<td>{$item.total|raw|pos_money}</td>
			</tr>
			{if $item.description}
			<tr>
				<td colspan="5">
					{$item.description|escape|nl2br}
				</td>
			</tr>
			{/if}
		{/foreach}
			<tr class="foot">
				<th>TVA</th>
				<td colspan="4"><em>Association exonérée des impôts commerciaux</em></td>
			</tr>
			<tr class="foot">
				<th colspan="4">Total</th>
				<td>{$tab.total|raw|pos_money}</td>
			</tr>
			{foreach from=$existing_payments item="payment"}
			<tr class="foot">
				<th>{$payment.method_name}</th>
				<td colspan="3"><em>Réf. {$payment.reference}</em></td>
				<td>{$payment.amount|raw|pos_money}</td>
			</tr>
			{/foreach}
			<tr class="foot">
				<th colspan="4">Déduction « Coup de pouce vélo - réparation »</th>
				<td>{$eligible|raw|pos_money}</td>
			</tr>
			{if $remainder_after}
			<tr class="foot">
				<th colspan="4">Reste à payer</th>
				<td>{$remainder_after|raw|pos_money}</td>
			</tr>
			{/if}
		</tbody>
	</table>
</section>

</body>
</html>