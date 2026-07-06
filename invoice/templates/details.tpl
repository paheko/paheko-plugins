{include file="_head.tpl" title=$title current="plugin_invoice"}

<nav class="tabs">
	{if $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_WRITE)}
	<aside>
		{linkbutton shape="plus" label="Dupliquer" href="duplicate.php?id=%s"|args:$invoice.id}
		{if $invoice->isDraft()}
			{linkbutton shape="delete" label="Supprimer" href="delete.php?id=%d"|args:$invoice.id target="_dialog"}
			{linkbutton shape="edit" label="Modifier" href="edit.php?id=%d"|args:$invoice.id target="_dialog"}
		{/if}
	</aside>
	{/if}
	{linkbutton shape="left" label="Retour à la liste" href="./"}
</nav>

{form_errors}

<form method="post" action="">

{if $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_WRITE)}
	{if $invoice.status === 'draft' && $invoice.total}
		<div class="alert block">
			<h3>Statut&nbsp;: brouillon</h3>
			<p class="submit">{button shape="check" name="validate" label="Valider" type="submit" class="main"}</p>
			<p>
				{if $invoice->isQuote()}
					En cliquant sur ce bouton, le devis sera verrouillé, il ne pourra plus être modifié, ni supprimé.
				{else}
					En cliquant sur ce bouton, la facture sera verrouillée, et ne pourra plus être modifiée, ni supprimée.
				{/if}
			</p>
		</div>
	{elseif $invoice->isQuote()}
		{if $invoice.status === $invoice::STATUS_AWAITING_SEND}
			<div class="alert block">
				<h3>Statut&nbsp;: à envoyer au client</h3>
				<p>{button shape="email" name="send" label="Envoyer" type="submit" class="main"}</p>
				<p>{button shape="check" name="mark_sent" label="Marquer comme envoyé" type="submit"}</p>
			</div>
		{elseif $invoice.status === $invoice::STATUS_AWAITING_VALIDATION}
			<div class="alert block">
				<h3>Statut&nbsp;: en attente de validation par le client</h3>
				<p>{button shape="delete" name="cancel" label="Annuler" type="submit"}</p>
				<p>{button shape="right" name="accept" label="Accepter et transformer en facture" type="submit"}</p>
			</div>
		{elseif $invoice.status === $invoice::STATUS_ACCEPTED}
			<div class="alert block">
				<h3>Statut&nbsp;: devis accepté</h3>
				<p>{button shape="delete" name="cancel" label="Annuler" type="submit"}</p>
			</div>
		{elseif $invoice.status === $invoice::STATUS_CANCELLED}
			<div class="alert block">
				<h3>Statut&nbsp;: annulé</h3>
			</div>
		{/if}
	{else}
		{if $invoice.status === $invoice::STATUS_AWAITING_SEND}
			<div class="alert block">
				<h3>Statut&nbsp;: à envoyer au client</h3>
				<p>{button shape="email" name="send" label="Envoyer" type="submit" class="main"}</p>
				<p>{button shape="check" name="mark_sent" label="Marquer comme envoyée" type="submit"}</p>
			</div>
		{elseif $invoice.status === $invoice::STATUS_AWAITING_PAYMENT}
			<div class="alert block">
				<h3>Statut&nbsp;: en attente de règlement</h3>
				<p>{button shape="check" name="mark_paid" label="Marquer comme payée" type="submit"}</p>
			</div>
		{elseif $invoice.status === $invoice::STATUS_AWAITING_PAYMENT}
			<div class="alert block">
				<h3>Statut&nbsp;: en attente de paiement</h3>
				<p>{linkbutton shape="plus" label="Saisir un paiement" href="payment.php?id=%s"|args:$invoice.id target="_dialog"</p>
			</div>
		{/if}
	{/if}
{/if}

<dl class="describe">
	<dt>Statut</dt>
	<dd>
		{tag label=$invoice->getStatusLabel() color=$invoice->getStatusColor()}
	</dd>
	<dt>Numéro</dt>
	<dd>{if $invoice->isDraft()}(En attente de validation){else}{$invoice.number}{/if}</dd>
	<dt>Objet</dt>
	<dd><h2>{$invoice.label}</h2></dd>
	<dt>Date</dt>
	<dd>{$invoice.date_created|date_short}</dd>
	<dt>Date d'échéance</dt>
	<dd>{$invoice.date_expiry|date_short}</dd>
	<dt>Client</dt>
	<dd>
		<strong>{$invoice->client()->name}</strong>
	</dd>
	{if $invoice.operation_type}
		<dt>Nature de la facture</dt>
		<dd>{$invoice->getOperationTypeLabel()}</dd>
	{/if}
	<dt>Notes</dt>
	<dd>{if $invoice.notes}{$invoice.notes|raw|markdown}{else}—{/if}</dd>

	{if $invoice.id_transaction}
	<dt>Écriture comptable</dt>
	<dd>{link class="num" href="!acc/transactions/details.php?id=%d"|args:$invoice.id_transaction label="#%d"|args:$invoice.id_transaction}</dd>
	{/if}

</dl>

{if $invoice->isDraft()}
	<p class="actions">
		{linkbutton shape="plus" label="Ajouter une ligne" href="line.php?id_invoice=%d"|args:$invoice.id target="_dialog"}
	</p>
{/if}

{if $export.lines}
	<table class="list">
		<thead>
			<tr>
				<th scope="col">Libellé</th>
				<td scope="col" class="money">Prix unitaire</td>
				<td scope="col" class="money">Quantité</td>
				<td scope="col" class="money">Total HT</td>
				<td scope="col" class="money">Taux TVA</td>
				<td scope="col" class="money">Total TTC</td>
				<td></td>
			</tr>
		</thead>
		<tbody>
		{foreach from=$export.lines item="line"}
			<tr>
				<td>
					<strong>{$line.item_information.name}</strong>
					{if $line.item_information.seller_identifier}
						<small>Réf. {$line.item_information.seller_identifier}</small>
					{/if}
					{if $line.item_information.description}
						<br /><em>{$line.item_information.description|escape|nl2br}</em>
					{/if}
				</td>
			<td class="money">{$line.price_details.item_net_price|raw|money_int|money_currency_html:false}</td>
			<td class="money">{$line.invoiced_quantity|unit:$line.invoiced_quantity_code:false} <small>{$line.invoiced_quantity_code|get_unit_label}</small></td>
			<td class="money">{$line.net_amount|raw|money_int|money_currency_html:false}</td>
			<td class="money">{$line.vat_information.invoiced_item_vat_rate|format_vat_rate}</td>
			<td class="money">{$line.line_with_vat_net_amount|raw|money_int|money_currency_html:false}</td>
			<td class="actions">
				{if $invoice->isDraft() && $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_WRITE)}
					{button name="delete_line" type="submit" value=$line.identifier label="Supprimer" shape="delete"}
					{linkbutton shape="edit" label="Modifier" href="line.php?id=%d"|args:$line.identifier}
				{/if}
			</td>
		</tr>
	{/foreach}
	</tbody>
	<tfoot>
		<tr>
			<th scope="row" colspan="5" class="total">Total HT</th>
			<td class="money">{$export.totals.total_without_vat|raw|money_int|money_currency_html:false}</td>
			<td></td>
		</tr>
		{foreach from=$export.vat_break_down item="vat"}
			<tr>
				<td scope="row" colspan="5" class="total">
					TVA à {$vat.vat_category_rate|format_vat_rate}
					{if $vat.vat_exemption_reason}
						<br />
						<small>TVA non applicable — {$vat.vat_exemption_reason}</small>
					{/if}
				</td>
				<td class="money">{$vat.vat_category_tax_amount|raw|money_int|money_currency_html:false}</td>
				<td></td>
			</tr>
		{/foreach}
		<tr>
			<th scope="row" colspan="5" class="total">Total TTC</th>
			<td class="money">{$export.totals.total_with_vat|raw|money_int|money_currency_html:false}</td>
			<td></td>
		</tr>
	</tfoot>
	</table>
{else}
	<p class="alert block">
		Aucune ligne dans ce document.<br />
	</p>
{/if}

{csrf_field key=$csrf_key}
</form>

{if $invoice->canPay() || $payments->count()}
	<h2 class="ruler">Paiements</h2>

	{if $payments->count()}
		{include file="common/dynamic_list_head.tpl" list=$payments disable_user_sort=true}
		{foreach from=$payments->iterate() item="payment"}
			<tr>
				<td class="num">{link class="num" href="!acc/transactions/details.php?id=%d"|args:$payment.id label="#%d"|args:$payment.id}</td>
				<td>{$payment.date|date_short}</td>
				<td>{$payment.label}</td>
				<td class="money">{$payment.credit|raw|money_currency_html}</td>
			</tr>
		{/foreach}
		</tbody>
		</table>
	{/if}

	{if $invoice->canPay() && $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_WRITE)}
		<p class="actions-center">
			{linkbutton shape="plus" label="Saisir un paiement" href="payment.php?id=%d"|args:$invoice.id target="_dialog"}
		</p>
	{/if}
{/if}

{include file="_foot.tpl"}