{include file="_head.tpl" title=$title current="plugin_invoice"}

{form_errors}

<form method="post" action="">

<nav class="tabs">
	<aside>
		{linkbutton shape="plus" label="Dupliquer" href="duplicate.php?id=%s"|args:$invoice.id}
		{if $invoice->isDraft()}
			{linkbutton shape="delete" label="Supprimer" href="delete.php?id=%d"|args:$invoice.id target="_dialog"}
			{linkbutton shape="edit" label="Modifier" href="edit.php?id=%d"|args:$invoice.id target="_dialog"}
		{/if}
	</aside>
	{linkbutton shape="left" label="Retour à la liste" href="./"}
</nav>

</form>

{form_errors}

<form method="post" action="">

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

{if $lines->count()}
	{include file="common/dynamic_list_head.tpl" list=$lines}
	{foreach from=$lines->iterate() item="line"}
		<tr>
			<td class="num">{$line.number}</td>
			<td>
				<strong>{$line.label}</strong>
				{if $line.reference}
					<small>Réf. {$line.reference}</small>
				{/if}
				{if $line.description}
					<br /><em>{$line.description|escape|nl2br}</em>
				{/if}
			</td>
			<td class="num">{$line.quantity} <small>{$line.unit_label}</small></td>
			<td class="money">{$line.price|raw|money_currency_html:false}</td>
			<td class="money">{$line.vat_rate}</td>
			<td class="money">{$line.total|raw|money_currency_html:false}</td>
			<td class="actions">
				{if $invoice->isDraft()}
					{button name="delete_line" type="submit" value=$line.id label="Supprimer" shape="delete"}
				{/if}
			</td>
		</tr>
	{/foreach}
	</tbody>
	</table>
{else}
	<p class="alert block">
		Aucune ligne dans ce document.<br />
	</p>
{/if}

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

	{if $invoice->canPay()}
		<p class="actions-center">
			{linkbutton shape="plus" label="Saisir un paiement" href="payment.php?id=%d"|args:$invoice.id target="_dialog"}
		</p>
	{/if}
{/if}

{include file="_foot.tpl"}