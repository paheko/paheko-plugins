{include file="_head.tpl" title="Calculette de fond de caisse" hide_title=true}

<style type="text/css">
{literal}
form > div {
	display: flex;
	flex-wrap: wrap;
	gap: 1em;
	justify-content: center;
	max-width: 60em;
	margin: 0 auto;
}
form > div fieldset {
	width: 45%;
}
form > div h2 {
	flex-basis: 100%;
}
.qty input {
	width: 5em !important;
	min-width: unset !important;
}
form {
	text-align: center;
}
.total {
	width: 4em;
	font-size: 1.2em;
}
span.note {
	border: 5px solid #99c;
	background: #eef;
	display: inline-flex;
	justify-content: center;
	align-items: center;
	height: 2em;
	width: 4em;
	line-height: 1em;
	font-size: 1.2em;
}
span.coin {
	display: inline-flex;
	justify-content: center;
	align-items: center;
	border: 5px solid #cc0;
	background: #ffc;
	border-radius: 100%;
	height: 2em;
	width: 2em;
	line-height: 1em;
	font-size: 1.2em;
}
span.coin.large {
	border-color: #999;
	background: #eee;
}
{/literal}
</style>

<form method="post" action="">
	<div>
		<fieldset>
			<legend>Billets</legend>
			<table class="list">
				<thead>
					<tr>
						<th scope="col">Valeur</th>
						<th scope="col">Qté</th>
						<th scope="col">Total</th>
					</tr>
				</thead>
				<tbody>
				{foreach from=$denominations.notes item="value"}
				<tr data-denomination="{$value}">
					<td><span class="note"><?=$value/100?></span></td>
					<td class="qty">{input type="number" name="" min=0 step=1}</td>
					<td class="total money"></td>
				</tr>
				{/foreach}
				</tbody>
				<tfoot>
					<tr>
						<td colspan="2">Total billets</td>
						<td class="total money"></td>
					</tr>
				</tfoot>
			</table>
		</fieldset>
		<fieldset>
			<legend>Pièces</legend>
			<table class="list">
				<thead>
					<tr>
						<th scope="col">Valeur</th>
						<th scope="col">Qté</th>
						<th scope="col">Total</th>
					</tr>
				</thead>
				<tbody>
				{foreach from=$denominations.coins item="value"}
				{if $value < 100}
					<?php $coin = number_format($value / 100, 2, ','); ?>
				{else}
					<?php $coin = $value / 100; ?>
				{/if}
				<tr data-denomination="{$value}">
					<td><span class="coin {if $value < 100}small{else}large{/if}">{$coin}</span></td>
					<td class="qty">{input type="number" name="" min=0 step=1}</td>
					<td class="total money"></td>
				</tr>
				{/foreach}
				</tbody>
				<tfoot>
					<tr>
						<td colspan="2">Total pièces</td>
						<td class="total money"></td>
					</tr>
				</tfoot>
			</table>
		</fieldset>
		<h2 class="ruler">
			Total&nbsp;: <span id="total"></span>
		</h2>
	</div>
	<p class="submit">
		<input type="hidden" name="id" value="{$id}" />
		{button type="submit" class="main" label="Reporter le total" shape="right"}
	</p>
</form>

<script type="text/javascript">
{literal}
var f = document.forms[0];
f.onsubmit = () => {
	var id = 'f_balances' + f.id.value;
	var bal = window.parent.document.getElementById(id);
	bal.value = $('#total').innerText;
	window.parent.g.closeDialog();
	return false;
};

function calculateTotal() {
	var totals = $('table tfoot .total');
	var total = g.getMoneyAsInt(totals[0].innerText) || 0;
	console.log(total);
	total += g.getMoneyAsInt(totals[1].innerText) || 0;
	$('#total').innerText = g.formatMoney(total);
}

function calculateTable(table) {
	var table_total = 0;

	var list = table.querySelectorAll('tbody tr');

	for (var i = 0; i < list.length; i++) {
		var row = list[i];
		var value = parseInt(row.dataset.denomination, 10);
		var qty = row.querySelector('.qty input');
		var total = parseInt(qty.value || 0, 10) * value;
		row.querySelector('.total').innerText = g.formatMoney(total);
		table_total += total;
	}

	table.querySelector('tfoot .total').innerText = g.formatMoney(table_total);
	calculateTotal();
}

$('fieldset table').forEach(t => {
	t.querySelectorAll('tbody tr .qty input').forEach(i => { i.oninput = () => calculateTable(t); });
});
{/literal}
</script>

{include file="_foot.tpl"}