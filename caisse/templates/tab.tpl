{include file="_head.tpl"}

<nav class="tabs">
	{if !$pos_session.closed}
		{linkbutton href="?session=%d&new"|args:$pos_session.id label="Nouvelle note" shape="plus"}
		{linkbutton href="session.php?id=%d"|args:$pos_session.id label="Résumé" shape="menu"}
		{linkbutton href="session_close.php?id=%d"|args:$pos_session.id label="Clôturer la caisse" shape="delete"}
	{/if}

	{if $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_ADMIN)}
		{linkbutton href="manage/" label="Gestion et statistiques" shape="settings"}
	{/if}

	<ul class="pos-tabs">
	{foreach from=$tabs item="tab"}
		<li class="tab {if $tab.id == $tab_id}current{/if} {if $tab.closed}closed{/if}">
			<a href="{$self_url_no_qs}?id={$tab.id}">
				{$tab.id}. {$tab.opened|date_hour}
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
				{$current_tab.opened|date_hour}
				{if $current_tab.closed}
				&rarr; {$current_tab.closed|date_hour}
				{/if}
				</h2>
				<h3>{$current_tab.name}</h3>
			</div>
			<div>
				<form method="post">
					{linkbutton title="Reçu" label=null shape="print" target="_dialog" href="./receipt.php?tab=%d"|args:$current_tab.id}
				{if $current_tab.user_id}
					{linkbutton href="!users/details.php?id=%d"|args:$current_tab.user_id label="" shape="user" target="_blank" title="Ouvrir la fiche membre"}
				{/if}
				{if !$remainder && !$current_tab.closed}
					{button type="submit" name="close" label="Clore la note" accesskey="C" shape="lock"}
				{elseif !count($existing_payments) && !count($items)}
					{button type="submit" name="delete" label="Supprimer la note" accesskey="D" shape="delete"}
				{elseif $current_tab.closed && !$pos_session.closed}
					{button type="submit" name="reopen" label="Ré-ouvrir la note" accesskey="C" shape="unlock"}
				{elseif !$current_tab.closed}
					{button type="submit" name="close" label="Clore la note" accesskey="C" shape="lock" disabled="disabled" title="La note ne peut être close, elle n'est pas soldée."}
				{/if}
					{button type="button" name="rename" label="Renommer" accesskey="R" shape="edit"}
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
					{if $has_weight}
					<td>Poids</td>
					{/if}
					<td class="money">Total</td>
					<td></td>
				</thead>
				<tbody>
				{foreach from=$items item="item"}
				<tr>
					<th><small class="cat">{$item.category_name}</small> {$item.name}
						{if !$current_tab.closed}<button title="Cliquer pour renommer" type="submit" value="{$item.name}" name="rename_item[{$item.id}]">{icon shape="edit"}</button>{/if}
					</th>
					<td>{if !$current_tab.closed}<input type="submit" name="change_qty[{$item.id}]" value="{$item.qty}" title="Cliquer pour changer la quantité" />{else}{$item.qty}{/if}</td>
					<td class="money">{if !$current_tab.closed}<button type="submit" title="Cliquer pour changer le prix unitaire" name="change_price[{$item.id}]">{$item.price|escape|money_currency:false}</button>{else}{$item.price|raw|money_currency:false}{/if}</td>
					{if $has_weight}
						<td class="money">
							{if !$current_tab.closed && $item.weight}
								<button type="submit" title="Cliquer pour changer le poids" name="change_weight[{$item.id}]">
									{$item.weight|format_weight:true:true}
								</button>
							{else}
								{$item.weight|format_weight:false:true}
							{/if}
						</td>
					{/if}
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
						{if $has_weight}
							<td></td>
						{/if}
						<td class="money">{$current_tab->total()|escape|money_currency:false}</td>
						<td></td>
					</tr>
					<tr>
						<th>{if $remainder < 0}<span class="error">Reste à rembourser</span>{else}Reste à payer{/if}</th>
						<td></td>
						<td></td>
						{if $has_weight}
							<td></td>
						{/if}
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
					<td class="actions">{if !$current_tab.closed}{linkbutton shape="delete" href="?id=%d&delete_payment=%d"|args:$current_tab.id,$payment.id title="Supprimer" label=""}{/if}</td>
				</tr>
				{/foreach}
				</tbody>
			</table>
			{/if}

			{if $remainder && count($payment_options)}
			<form method="post">
				<fieldset>
					<legend>
						{if $remainder < 0}
							Reste {$remainder|escape|abs|money_currency} à rembourser
						{else}
							Reste {$remainder|escape|money_currency} à payer
						{/if}
					</legend>
					<dl>
						<dt><label for="f_method_id">Moyen de paiement</label></dt>
						<dd>
							<select name="method_id" id="f_method_id">
								{foreach from=$payment_options item="method"}
								<option value="{$method.id}" data-amount="{$method.amount|money_raw}" data-iscash="{$method.is_cash}">{$method.name} (jusqu'à {$method.amount|escape|money_currency:false})</option>
								{/foreach}
							</select>
						</dd>
						{input type="money" label="Montant" default=$remainder required=true name="amount"}
					</dl>
					<dl class="reference">
						{input type="text" label="Référence du paiement (numéro de chèque…)" name="reference"}
					</dl>
					<p class="submit">
						{button type="submit" name="pay" label="Enregistrer le paiement" shape="right" class="main" accesskey="P"}
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
		<ul>
			<li {if !$selected_cat} class="current"{/if}><a href="?id={$tab.id}" data-cat=""><strong>Tout afficher</strong></a></li>
			<?php $h = -45; ?>
			{foreach from=$products_categories item="cat"}
				<?php $h += 30; if ($h > 360) $h = 0; ?>
				<li {if $selected_cat == $cat.id} class="current"{/if} style="--cat-hue: {$h};"><a href="?id={$tab.id}" data-cat="{$cat.id}">{$cat.name}</a></li>
			{/foreach}
		</ul>
		<form method="post" action="">
		<?php $h = -45; ?>
		{foreach from=$products_categories item="cat"}
			<?php $h += 30; if ($h > 360) $h = 0; ?>
			<section data-cat="{$cat.id}" {if $selected_cat && $selected_cat != $cat.id} class="hidden"{/if} style="--cat-hue: {$h};">
				<h2 class="ruler">{$cat.name}</h2>

				<div>
				{foreach from=$cat.products item="product"}
					<button name="add_item[{$product.id}]" {if $product.weight < 0}data-ask-weight="true"{/if} data-code="{$product.code}" value="">
						<h3>{$product.name}</h3>
						<h4>{$product.price|escape|money_currency}</h4>
						{if $product.image}
							<figure>{*TODO*}</figure>
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
	<div id="user_rename_list">
	</div>
</div>

<script type="text/javascript" src="{$plugin_admin_url}tab.js" async="async"></script>
{/if}

{include file="_foot.tpl"}