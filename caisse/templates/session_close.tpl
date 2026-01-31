{include file="_head.tpl"}

{if $open_notes}
<p class="error block">
	Cette caisse a des notes qui ne sont pas closes, il ne sera pas possible de la clôturer.
</p>
{/if}

{form_errors}

{if count($missing_users)}
	<div class="error block">
		<h3>Notes sans membres</h3>
		<p>Les notes suivantes comportent des inscriptions à des activités, mais aucun membre lié&nbsp;:</p>
		<ul>
			{foreach from=$missing_users item="id"}
			<li>{link href="tab.php?id=%d"|args:$id label="Note n°%d"|args:$id}</li>
			{/foreach}
		</ul>
		<p>Merci d'associer ces notes à des membres pour pouvoir clôturer la caisse.</p>
	</div>
{/if}

<form method="post" action="" data-focus="1">

<h2 class="ruler">1. Vérifier la caisse</h2>

{if !count($balances)}
	<p class="help">Il n'y aucun moyen de paiement nécessitant de compter la caisse.</p>
{else}
	<div class="pos-balances">
		{foreach from=$balances item="balance"}
		<fieldset>
			<legend>{$balance.name}</legend>
			<dl>
				<dt>Solde à l'ouverture</dt>
				<dd class="info"><tt>{$balance.open_amount|raw|money_currency_html:false}</tt></dd>
				<dt>Solde théorique à la fermeture</dt>
				<dd class="info"><strong><tt>{$balance.expected_total|raw|money_currency_html:false}</tt></strong></dd>
				<dd class="info">
					{assign var="amount" value=$balance.total|money_raw}
					{if !$balance.total}
						{tag color="darkgreen" label="identique"} à l'ouverture
					{elseif $balance.expected_total > $balance.open_amount}
						{tag color="darkcyan" label="+%s"|args:$amount} par rapport à l'ouverture
					{else}
						{tag color="darkorange" label="-%s"|args:$amount} par rapport à l'ouverture
					{/if}
				</dd>
				{input type="money" name="balances[%d][amount]"|args:$balance.id data-expected=$balance.expected_total required=true label="Solde constaté à la fermeture" help="Merci de compter le contenu de la caisse."}
			</dl>
			<div class="balance-error hidden">
				<p class="error block">
					Erreur de caisse de
					<strong class="balance-diff"></strong>&nbsp;€.
					Merci de bien vouloir recompter la caisse.
				</p>
				<p class="help">
					{input type="checkbox" name="balances[%d][confirm]"|args:$balance.id value="1" label="Je confirme avoir re-compté le contenu de la caisse et constate toujours une erreur."}
				</p>
			</div>
		</fieldset>
		{/foreach}
	</div>
{/if}

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
			{input type="checkbox" name="confirm" value="1" label="Je confirme que les informations indiquées dans ce formulaire sont justes" class="alert" required=true}
		</dl>
	</dl>
</fieldset>

<p class="alert block">
	Il ne sera plus possible de corriger les informations une fois la caisse clôturée.
</p>

<p class="submit">
	{csrf_field key=$csrf_key}
	{button type="submit" name="close" label="Clôturer la caisse" shape="lock" class="main"}
</p>

</form>

<script type="text/javascript">
{literal}
document.querySelectorAll('.pos-balances fieldset').forEach(f => {
	var amount = f.querySelector('input');
	var block = f.querySelector('.balance-error')
	var diffElement = f.querySelector('.balance-error .balance-diff');

	amount.onkeyup = (e) => {
		var expected = parseInt(amount.getAttribute('data-expected'), 10);
		var typed_value = amount.value.replace(/[^\d.,]/g, '');

		if (!typed_value.length) {
			return;
		}

		typed_value = g.getMoneyAsInt(typed_value);

		if (expected === typed_value) {
			g.toggle(block, false);
			return;
		}

		g.toggle(block, true);

		var diff = typed_value - expected;

		var sign = diff < 0 ? '-' : '+';
		diffElement.innerText = sign + g.formatMoney(Math.abs(diff), true);
	};
});

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