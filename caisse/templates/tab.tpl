{include file="admin/_head.tpl" current="plugin_%s"|args:$plugin.id}

<ul class="actions">
	<li><a href="{$self_url_no_qs}?new"><strong>Nouvelle note</strong></a></li>
{foreach from=$tabs item="tab"}
	<li class="{if $tab.id == $tab_id}current{/if} {if $tab.closed}closed{/if}">
		<a href="{$self_url_no_qs}?id={$tab.id}">
			{$iteration}. {$tab.opened|date_format:"%H:%M"}
			{if $tab.total} — {$tab.total|escape|pos_money}{/if}
			{if $tab.name} — {$tab.name}{/if}
		</a>
	</li>
{/foreach}
</ul>

{if $tab_id}
<section class="pos">
	<section class="tab">
		<header>
			<h2>
				{$current_tab.opened|date_format:"%H:%M"}
				{if $current_tab.closed}
				&rarr; {$current_tab.closed|date_format:"%H:%M"}
				{/if}
				— {$current_tab.name}
			</h2>
			<div>
				<form method="post">
				<input type="submit" name="rename" value="Renommer" />
				{if !$remainder && $items && !$current_tab.closed}
					<input type="submit" name="close" value="Clore la note" />
				{/if}
				{if !count($existing_payments)}
					<input type="submit" name="delete" value="Supprimer la note" />
				{/if}
				</form>
				<form method="post" action="./pdf.php?id={$current_tab.id}" id="f_pdf">
					<input type="submit" data-name="{if $current_tab.name}1{else}0{/if}" value="Facture PDF" />
				</form>
			</div>
		</header>

		<section class="items">
			<form method="post">
			<table class="list">
				<thead>
					<th></th>
					<td>Qté</td>
					<td>Prix</td>
					<td>Total</td>
					<td></td>
				</thead>
				<tbody>
				{foreach from=$items item="item"}
				<tr>
					<th>{$item.name} {$item.methods|raw|show_methods}</th>
					<td>{if !$current_tab.closed}<input type="submit" name="change_qty[{$item.id}]" value="{$item.qty}" />{else}{$item.qty}{/if}</td>
					<td>{if !$current_tab.closed}<input type="submit" name="change_price[{$item.id}]" value="{$item.price|escape|pos_money}" />{else}{$item.price|escape|pos_money}{/if}</td>
					<td>{$item.total|escape|pos_money}</td>
					<td class="actions">{if !$current_tab.closed}<a class="icn" href="?id={$current_tab.id}&amp;delete_item={$item.id}" title="Supprimer">✘</a>{/if}</td>
				</tr>
				{/foreach}
				</tbody>
				<tfoot>
					<tr>
						<th>Total</th>
						<td></td>
						<td></td>
						<td colspan="2">{$current_tab.total|escape|pos_money}</td>
					</tr>
					<tr>
						<th>Reste à payer</th>
						<td></td>
						<td></td>
						<td colspan="2">{$remainder|raw|pos_money}</td>
					</tr>
					<tr>
						<th colspan="3">dont éligible Coup de pouce vélo
						<em>(min: 15 €, max: 50 €)</em></th>
						<td colspan="2">{$eligible_alveole|escape|pos_money}</td>
					</tr>
				</tfoot>
			</table>
		</form>
		</section>

		<section class="payments">
			{if $existing_payments}
			<h2>Paiements effectués</h2>
			<table class="list">
				<tbody>
				{foreach from=$existing_payments item="payment"}
				<tr>
					<th>{$payment.name}</th>
					<td>{$payment.amount|escape|pos_money}</td>
					<td><em>{$payment.reference}</em></td>
					<td class="actions">{if !$current_tab.closed}<a class="icn" href="?id={$current_tab.id}&amp;delete_payment={$payment.id}" title="Supprimer">✘</a>{/if}</td>
				</tr>
				{/foreach}
				</tbody>
			</table>
			{/if}

			{if $remainder}
			<form method="post">
				<fieldset>
					<legend>Reste {$remainder|escape|pos_money} à payer</legend>
					<dl>
						<dt>Moyen de paiement</dt>
						<dd>
							<select name="method_id">
								{foreach from=$payment_options item="method"}
								<option value="{$method.id}" data-amount="{$method.amount|pos_amount}">{$method.name} (jusqu'à {$method.amount|escape|pos_money})</option>
								{/foreach}
							</select>
						</dd>
						<dt>Montant</dt>
						<dd>
							<input type="text" pattern="\d+(,\d+)?" name="amount" id="f_method_amount" value="{$remainder|pos_amount}" required="required" size="5" /> €
						</dd>
						<dt>Référence du paiement (numéro de chèque…)</dt>
						<dd>
							<input type="text" name="reference" />
						</dd>
					</dl>
					<p class="submit">
						<input type="submit" name="pay" value="Enregistrer le paiement" />
					</p>
				</fieldset>
			</form>
			{/if}
		</section>
	</section>

	{if !$current_tab.closed}
	<section class="products">
		<form method="get" action="">
			<input type="text" name="q" placeholder="Recherche rapide" />
		</form>
		<form method="post" action="">
		{foreach from=$products_categories key="category" item="products"}
			<section>
				<h2 class="ruler">{$category}</h2>

				<div>
				{foreach from=$products item="product"}
					<button name="add_item[{$product.id}]">
						<h3>{$product.name}</h3>
						<h4>{$product.price|escape|pos_money}</h4>
						{if $product.image}
							<figure><img src="{$product.image|image_base64}" alt="" /></figure>
						{/if}
					</button>
				{/foreach}
				</div>
			</section>
		{/foreach}
		</form>
	</section>
	{/if}
</section>
{/if}

<script type="text/javascript">
{literal}
var fr = document.querySelector('input[name="rename"]');

if (fr) {
	fr.onclick = function(e) {
		fr.value = prompt("Nouveau nom ?");
	}
}

document.querySelectorAll('input[name*="change_qty"], input[name*="change_price"]').forEach((elm) => {
	elm.onclick = (e) => {
		var v = prompt('?');
		if (v === null) return false;
		elm.value = v;
	};
});

var pm = document.querySelector('select[name="method_id"]');

if (pm) {
	pm.onchange = (e) => {
		document.querySelector('#f_method_amount').value = pm.options[pm.selectedIndex].getAttribute('data-amount');
	};
}

var q = document.querySelector('input[name="q"]');

RegExp.escape = function(string) {
  return string.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&')
};

function normalizeString(str) {
	return str.normalize('NFD').replace(/[\u0300-\u036f]/g, "")
}

if (q) {
	q.onkeyup = (e) => {
		var search = new RegExp(RegExp.escape(normalizeString(q.value)), 'i');

		document.querySelectorAll('.products button h3').forEach((elm) => {
			if (normalizeString(elm.innerText).match(search)) {
				elm.parentNode.style.display = null;
			}
			else {
				elm.parentNode.style.display = 'none';
			}
		})
	};

	q.focus();
}

var pdf = document.getElementById('f_pdf');
pdf.onsubmit = (e) => {
	if (pdf.querySelector('input').getAttribute('data-name') == 0) {
		alert("Merci de donner un nom à la facture d'abord.");
		return false;
	}
};
{/literal}
</script>

{include file="admin/_foot.tpl"}