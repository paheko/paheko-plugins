{include file="_head.tpl" title=$title current="plugin_invoice"}

<nav class="tabs">
	<aside>
		{linkbutton shape="eye" label="Visualiser" href="?id=%d&print"|args:$invoice.id target="_dialog"}
		{if $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_WRITE)
			&& !$invoice.provider_name}
			{linkbutton shape="delete" label="Supprimer" href="delete.php?id=%d"|args:$invoice.id target="_dialog"}
			{linkbutton shape="edit" label="Modifier" href="edit.php?id=%d"|args:$invoice.id target="_dialog"}
		{/if}
		{linkbutton shape="download" label="Télécharger" href="?id=%d&download"|args:$invoice.id}
	</aside>
	{linkbutton shape="left" label="Retour à la liste" href="./"}
</nav>

{form_errors}

<form method="post" action="">

<dl class="describe">
	<dt>Type</dt>
	<dd>{$invoice->getTypeLabel()}</dd>
	<dt>Statut</dt>
	<dd>
		{tag label=$invoice->getStatusLabel() status=$invoice->getStatusColor()}
	</dd>
	<dt>Numéro</dt>
	<dd>{if $invoice->isDraft()}(En attente de validation){else}{$invoice->getReference()}{/if}</dd>
	<dt>Date</dt>
	<dd>{$invoice.issue_date|date_short}</dd>
	<dt>Date d'échéance</dt>
	<dd>{$invoice.due_date|date_short}</dd>
	<dt>Émetteur de la facture</dt>
	<dd>
		<strong>{$invoice.person_name}</strong>
	</dd>
	<dt>Adresse</dt>
	{if $person.postal_address.address_line1}
		<dd>{$person.postal_address.address_line1}</dd>
	{/if}
	{if $person.postal_address.address_line2}
		<dd>{$person.postal_address.address_line2}</dd>
	{/if}
	{if $person.postal_address.address_line3}
		<dd>{$person.postal_address.address_line3}</dd>
	{/if}
	{if $person.postal_address.post_code || $person.postal_address.city}
		<dd>{$person.postal_address.post_code} {$person.postal_address.city}</dd>
	{/if}
	<dd>{$person.postal_address.country_code|get_country_name}</dd>

	{*FIXME: add SIREN/SIRET*}

	<dt>Notes</dt>

	{foreach from=$export.notes item="note"}
		<dd>{$note.note|escape|nl2br}</dd>
	{/foreach}

	{if $invoice.id_transaction}
	<dt>Écriture comptable</dt>
	<dd>{link class="num" href="!acc/transactions/details.php?id=%d"|args:$invoice.id_transaction label="#%d"|args:$invoice.id_transaction}</dd>
	{/if}
</dl>

{if $invoice.provider_name}
	<p class="alert block">
		Document généré automatiquement à partir d'une facture électronique.<br />
		La facture électronique archivée par votre plateforme agréée demeure le seul document légal faisant foi.
	</p>
{/if}

{if $export.lines}
	{include file="../_lines.tpl"}
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