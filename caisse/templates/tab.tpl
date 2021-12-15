{include file="admin/_head.tpl" current="plugin_%s"|args:$plugin.id}

<nav class="tabs">
	{if !$pos_session.closed}
		{linkbutton href="?new" label="Nouvelle note" shape="plus"}
		{linkbutton href="session.php?id=%d"|args:$pos_session.id label="Résumé" shape="menu"}
		{linkbutton href="session_close.php?id=%d"|args:$pos_session.id label="Clôturer la caisse" shape="delete"}
	{/if}

	<ul class="pos-tabs">
	{foreach from=$tabs item="tab"}
		<li class="tab {if $tab.id == $tab_id}current{/if} {if $tab.closed}closed{/if}">
			<a href="{$self_url_no_qs}?id={$tab.id}">
				{$tab.id}. {$tab.opened|date_format:"%H:%M"}
				{if $tab.total} — {$tab.total|escape|money_currency}{/if}
				{if $tab.name} — {$tab.name}{/if}
			</a>
		</li>
	{/foreach}
	</ul>
</nav>

{if $tab_id}
<section class="pos">
	<section class="tab">
		<header>
			<div>
				<h2>
				{$current_tab.id}.
				{$current_tab.opened|date_format:"%H:%M"}
				{if $current_tab.closed}
				&rarr; {$current_tab.closed|date_format:"%H:%M"}
				{/if}
				</h2>
				<h3>{$current_tab.name}</h3>
			</div>
			<div>
				<form method="post">
				{if $current_tab.user_id}
					{linkbutton href="!membres/fiche.php?id=%d"|args:$current_tab.user_id label="" shape="user" target="_blank" title="Ouvrir la fiche membre"}
				{/if}
				{if !$remainder && !$current_tab.closed}
					<input type="submit" name="close" value="Clore la note" />
				{elseif !count($existing_payments) && !count($items)}
					<input type="submit" name="delete" value="Supprimer la note" />
				{elseif $current_tab.closed && !$pos_session.closed}
					<input type="submit" name="reopen" value="Ré-ouvrir la note" />
				{elseif !$current_tab.closed}
					<input type="button" name="reopen" value="Clore la note" disabled="disabled" title="La note ne peut être close, elle n'est pas soldée." />
				{/if}
				<input type="button" name="rename" value="Renommer" />
				</form>
				<form method="post" action="./pdf.php?id={$current_tab.id}" id="f_pdf">
					<input type="submit" data-name="{if $current_tab.name}1{else}0{/if}" name="receipt" value="Reçu PDF" />
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
					<td class="money">Total</td>
					<td></td>
				</thead>
				<tbody>
				{foreach from=$items item="item"}
				<tr>
					<th><small class="cat">{$item.category_name}</small> {$item.name}</th>
					<td>{if !$current_tab.closed}<input type="submit" name="change_qty[{$item.id}]" value="{$item.qty}" title="Cliquer pour changer la quantité" />{else}{$item.qty}{/if}</td>
					<td>{if !$current_tab.closed}<button type="submit" title="Cliquer pour changer le prix unitaire" name="change_price[{$item.id}]">{$item.price|escape|money_currency:false}</button>{else}{$item.price|raw|money_currency:false}{/if}</td>
					<td class="money">{$item.total|escape|money_currency:false}</td>
					<td class="actions">
						{if !$current_tab.closed}
							{linkbutton label="" shape="delete" href="?id=%d&delete_item=%d"|args:$current_tab.id,$item.id title="Cliquer pour supprimer la ligne"}
						{/if}
					</td>
				</tr>
				{/foreach}
				</tbody>
				<tfoot>
					<tr>
						<th>Total</th>
						<td></td>
						<td></td>
						<td class="money">{$current_tab.total|escape|money_currency:false}</td>
						<td></td>
					</tr>
					<tr>
						<th>Reste à payer</th>
						<td></td>
						<td></td>
						<td class="money">{$remainder|raw|money_currency:false}</td>
						<td></td>
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
					<th>{$payment.method_name}</th>
					<td>{$payment.amount|escape|money_currency}</td>
					<td><em>{$payment.reference}</em></td>
					<td class="actions">{if !$current_tab.closed}<a class="icn" href="?id={$current_tab.id}&amp;delete_payment={$payment.id}" title="Supprimer">✘</a>{/if}</td>
				</tr>
				{/foreach}
				</tbody>
			</table>
			{/if}

			{if $remainder && count($payment_options)}
			<form method="post">
				<fieldset>
					<legend>Reste {$remainder|escape|money_currency} à payer</legend>
					<dl>
						<dt><label for="f_method_id">Moyen de paiement</label></dt>
						<dd>
							<select name="method_id" id="f_method_id">
								{foreach from=$payment_options item="method"}
								<option value="{$method.id}" data-amount="{$method.amount|pos_amount}">{$method.name} (jusqu'à {$method.amount|escape|money_currency})</option>
								{/foreach}
							</select>
						</dd>
						{input type="money" label="Montant" default=$remainder required=true name="amount"}
						{input type="text" label="Référence du paiement (numéro de chèque…)" name="reference"}
					</dl>
					<p class="submit">
						{button type="submit" name="pay" label="Enregistrer le paiement" shape="right" class="main"}
					</p>
				</fieldset>
			</form>
			{elseif $remainder > 0}
				<p class="error block">Aucun moyen de paiement possible : certains produits n'ont aucun moyen de paiement défini.</p>
			{elseif $remainder < 0}
				<p class="error block">Des paiements ont été enregistrés, mais il n'y a pas de produits dans la note.</p>
			{/if}
		</section>
	</section>

	{if !$current_tab.closed}
	<section class="products">
		<input type="text" name="q" placeholder="Recherche rapide" />
		<form method="post" action="">
		{foreach from=$products_categories key="category" item="products"}
			<section>
				<h2 class="ruler">{$category}</h2>

				<div>
				{foreach from=$products item="product"}
					<button name="add_item[{$product.id}]">
						<h3>{$product.name}</h3>
						<h4>{$product.price|escape|money_currency}</h4>
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

{if $tab_id}
<div id="user_rename" class="hidden">
	<form method="post" action="{$self_url}">
		<input type="hidden" name="rename_id" value="" />
		<input type="text" name="rename_name" placeholder="Chercher un membre…" value="{$current_tab.name}" />
	</form>
	<ul>
	</ul>
</div>

<script type="text/javascript">
{literal}
var fr = document.querySelector('input[name="rename"]');


var ur = $('#user_rename');
var ur_input = $(' #user_rename input[type=text]')[0];
var ur_id = $('[name="rename_id"]')[0];
var ur_list = $(' #user_rename ul')[0];
var ur_list_template = '';
var ur_timeout = null;

if (fr) {
	fr.onclick = function(e) {
		g.toggle(' #user_rename', true);
		ur_input.focus();
		ur_input.select();
		return false;
	}
}

ur.onclick = (e) => {
	if (e.target === ur) closeUserRename();
};

function closeUserRename () {
	g.toggle(' #user_rename', false);
	ur_input.value = '';
	$(' #user_rename ul')[0].innerHTML = '';
	return false;
}

function selectUserRename (id, name) {
	closeUserRename();
	ur_id.value = id;
	ur_input.value = name;
	ur_input.form.submit();
	return false;
}

function completeUserName(list) {
	var v = ur_input.value.replace(/^\s+|\s+$/g, '');

	if (!v.match(/^\d+$/) && v.length < 3) return false;

	fetch(g.admin_url + 'plugin/caisse/_member_search.php?q=' + encodeURIComponent(v))
		.then(response => response.text())
		.then(list => ur_list.innerHTML = list );
}

ur_input.onkeyup = (e) => {
	window.clearTimeout(ur_timeout);
	ur_timeout = window.setTimeout(completeUserName, 300);
	return false;
};

document.querySelectorAll('input[name*="change_qty"], button[name*="change_price"]').forEach((elm) => {
	elm.onclick = (e) => {
		var v = prompt('?', elm.value);
		if (v === null) return false;
		elm.value = v;
	};
});

var pm = document.querySelector('select[name="method_id"]');

if (pm) {
	pm.onchange = (e) => {
		var o = pm.options[pm.selectedIndex];
		document.querySelector('#f_amount').value = o.getAttribute('data-amount');
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
{/if}

{include file="admin/_foot.tpl"}