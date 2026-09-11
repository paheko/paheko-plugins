{include file="_head.tpl" title=$title current="plugin_invoice"}

<nav class="tabs">
	{if $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_WRITE)}
	<aside>
		{linkbutton shape="eye" label="Visualiser" href="?id=%d&print"|args:$invoice.id target="_dialog"}
		{if $invoice->type !== $invoice::TYPE_CREDIT}
			{linkbutton shape="plus" label="Dupliquer" href="duplicate.php?id=%d"|args:$invoice.id}
		{/if}
		{if $invoice->isDraft()}
			{linkbutton shape="delete" label="Supprimer" href="delete.php?id=%d"|args:$invoice.id target="_dialog"}
			{linkbutton shape="edit" label="Modifier" href="edit.php?id=%d"|args:$invoice.id target="_dialog"}
		{elseif $facturx_enabled}
			{linkbutton shape="download" label="Télécharger" href="?id=%d&download"|args:$invoice.id}
		{/if}
	</aside>
	{/if}
	{linkbutton shape="left" label="Retour à la liste" href="./"}
</nav>

{form_errors}

<form method="post" action="">

{if $_GET.msg === 'ACCEPTED'}
	<p class="confirm block">
		Le devis a été accepté, voici la facture créée à partir du devis.<br />
		Vérifiez que la facture est conforme avant de la valider.
	</p>
{elseif $_GET.msg === 'CREDIT'}
	<p class="confirm block">
		La facture a été annulée, voici l'avoir correspondant.<br />
		Vérifiez les montants à rembourser avant de valider.
	</p>
{/if}

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
				<p>
					{button shape="mail" name="send_email" label="Envoyer par e-mail" type="submit" class="main"}
					{linkbutton shape="download" label="Télécharger" href="?id=%d&download"|args:$invoice.id}
					{button shape="check" name="mark_sent" label="Marquer comme envoyé" type="submit"}
				</p>
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
		{/if}
	{else}
		{if $invoice.status === $invoice::STATUS_AWAITING_SEND}
			<div class="alert block">
				<h3>Statut&nbsp;: à envoyer au client</h3>
				<p>
					{button shape="mail" name="send_email" label="Envoyer par e-mail" type="submit" class="main"}
					{linkbutton shape="download" label="Télécharger" href="?id=%d&download"|args:$invoice.id}
					{button shape="check" name="mark_sent" label="Marquer comme envoyée" type="submit"}
				</p>
			</div>
		{elseif $invoice.status === $invoice::STATUS_AWAITING_PAYMENT}
			<div class="alert block">
				<h3>Statut&nbsp;: en attente de règlement</h3>
				<p>Envoyée le {$invoice.date_sent|date_short}</p>
				{*<p>{linkbutton shape="plus" label="Saisir un paiement" href="payment.php?id=%s"|args:$invoice.id target="_dialog"}</p>*}
				<p>
					{button shape="check" name="mark_paid" label="Marquer comme payée" type="submit"}
					{button shape="delete" name="cancel" label="Annuler" type="submit"}
				</p>
			</div>
		{elseif $invoice.status === $invoice::STATUS_AWAITING_REFUND}
			<div class="alert block">
				<h3>Statut&nbsp;: en attente de remboursement</h3>
				{*<p>{linkbutton shape="plus" label="Saisir un remboursement" href="refund.php?id=%s"|args:$invoice.id target="_dialog"}</p>*}
				<p>
					{button shape="check" name="mark_refunded" label="Marquer comme remboursé" type="submit"}
				</p>
			</div>
		{/if}
	{/if}
{/if}

<dl class="describe">
	<dt>Type</dt>
	<dd>{$invoice->getTypeLabel()}</dd>
	<dt>Statut</dt>
	<dd>
		{tag label=$invoice->getStatusLabel() status=$invoice->getStatusColor()}
	</dd>
	<dt>Numéro</dt>
	<dd>{if $invoice->isDraft()}(En attente de validation){else}{$invoice->getReference()}{/if}</dd>
	<dt>Objet</dt>
	<dd><h2>{$invoice.label}</h2></dd>
	<dt>Date</dt>
	<dd>{$invoice.date_created|date_short}</dd>
	<dt>Date d'échéance</dt>
	<dd>{$invoice.date_expiry|date_short}</dd>
	<dt>Client</dt>
	<dd>
		<strong>{$client.name}</strong>
	</dd>
	{if $invoice.id_user}
	<dd>
		{linkbutton shape="user" href="!users/details.php?id=%d"|args:$client.id_user label="Voir la fiche de membre"}
	</dd>
	{/if}
	{if $invoice.operation_type}
		<dt>Nature de la facture</dt>
		<dd>{$invoice->getOperationTypeLabel()}</dd>
	{/if}
	<dt>Notes</dt>
	<dd>{if $invoice.notes}{$invoice.notes|escape|nl2br}{else}—{/if}</dd>

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
	{include file="./_lines.tpl"}
{else}
	<p class="alert block">
		Aucune ligne dans ce document.<br />
	</p>
{/if}

{csrf_field key=$csrf_key}
</form>

{*
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
*}

{include file="_foot.tpl"}