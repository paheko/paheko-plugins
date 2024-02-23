{include file="_head.tpl" title="Clôturer la caisse"}

{if $open_notes}
<p class="error block">
	Cette caisse a des notes qui ne sont pas closes, il ne sera pas possible de la clôturer.
</p>
{/if}

<form method="post" action="" data-focus="1">

<h2 class="ruler">1. Vérifier la caisse</h2>

<fieldset>
	<legend>Contenu de la caisse</legend>
	<dl>
		<dt>Ouverture</dt>
		<dd>Caisse ouverte le {$pos_session.opened|date_long:true}</dd>
		<dt>Solde à l'ouverture</dt>
		<dd>{$pos_session.open_amount|raw|money_currency}</dd>
		<dt>Solde théorique à la fermeture</dt>
		<dd><strong>{$close_total|raw|money_currency}</strong> ({$cash_total|raw|money:false:true} par rapport à l'ouverture)</dd>
		{input type="money" name="amount" data-expected=$close_total required=true label="Solde constaté à la fermeture" help="Merci de compter le contenu de la caisse lors de la fermeture."}
	</dl>
</fieldset>

<div class="cash_diff" style="display: none">
	<p class="error block">
		Erreur de caisse de
		<strong id="cash_diff_count" /></strong>&nbsp;€.
		Merci de bien vouloir recompter la caisse.
	</p>
	<p class="help">
		{input type="checkbox" name="recheck" value="1" label="Je confirme avoir re-compté le contenu de la caisse et constate toujours une erreur."}
	</p>
</div>

<h2 class="ruler">2. Vérifier les paiements hors espèces (chèques, carte, etc.)</h2>

{if !count($payments_except_cash)}
<p class="help">Aucun paiement à vérifier :-)</p>
{else}
<fieldset>
	<legend>Cocher les paiements</legend>
	<p class="help">
		Cocher chacun des paiements reçus (chèques, paiement en carte), en vérifiant la correspondance du montant et de la référence.<br />
		En cas d'erreur de saisie, ré-ouvrir la note associée pour corriger.
	</p>
	<table class="list">
		<thead>
			<tr>
				<td></td>
				<td>Note n°</td>
				<td>Heure</td>
				<td>Moyen de paiement</td>
				<td>Montant</td>
				<td>Référence</td>
				<td></td>
			</tr>
		</thead>
		<tbody>
			{foreach from=$payments_except_cash item="payment"}
			<tr>
				<td class="check">
					{input type="checkbox" name="payments[%d]"|args:$payment.id value="1"}
				</td>
				<td>{$payment.tab}</td>
				<td>
					{$payment.date|date_hour}
				</td>
				<td>{$payment.method_name}</td>
				<th>
					{$payment.amount|raw|money_currency}
				</th>
				<td>{$payment.reference}</td>
				<td class="actions">
					{linkbutton shape="menu" label="Note" href="tab.php?id=%d"|args:$payment.tab}
				</td>
			</tr>
			{/foreach}
		</tbody>
	</table>
	<p class="help">Vérifier également qu'il n'y a pas de chèque ou paiement par carte qui ne figurerait pas dans cette liste.</p>
</fieldset>
{/if}

<h2 class="ruler">3. Confirmer et clôturer</h2>

<fieldset>
	<dl>
		<dl>
			{if $plugin.config.allow_custom_user_name}
				{input type="text" name="user_name" label="Nom de la personne clôturant la caisse" required=true default=$user_name}
			{/if}
			{input type="checkbox" name="confirm" value="1" label="Je confirme que les informations indiquées dans ce formulaire sont justes" class="alert"}
		</dl>
	</dl>
</fieldset>

<p class="alert block">
	Il ne sera plus possible de corriger les informations une fois la caisse clôturée.
</p>

<p class="submit">
	{button type="submit" name="close" label="Clôturer la caisse" shape="lock" class="main"}
</p>

</form>

<script type="text/javascript">
{literal}
var a = document.getElementById('f_amount');

a.onkeyup = (e) => {
	var amount = a.value.replace(/[^\d.,]/g, '');
	if (!amount.length) return;

	var expected = parseInt(a.getAttribute('data-expected'), 10);

	amount = amount.replace(',', '.');
	amount = amount.split('.');
	amount = parseInt(amount[0], 10) + ((amount[1] || '') + '00').substr(0, 2);
	amount = parseInt(amount, 10);

	if (expected === amount) {
		document.querySelector('.cash_diff').style.display = 'none';
		return;
	}

	document.querySelector('.cash_diff').style.display = 'block';

	var diff = amount - expected;
	var sign = diff.toString().substr(0, 1) == '-' ? '-' : '+';
	diff = Math.abs(diff).toString();
	diff = sign + (diff.substr(0, diff.length - 2) || '0') + ',' + ('00' + diff.substr(-2)).substr(-2);
	document.getElementById('cash_diff_count').innerText = diff;
};

document.querySelectorAll('tbody tr td.check input[type=checkbox]').forEach((elm) => {
	elm.onclick = (e) => {
		var row = elm;
		while (!((row = row.parentNode) instanceof HTMLTableRowElement));
		row.classList.toggle('checked');
	};
});
{/literal}
</script>

{include file="_foot.tpl"}