{include file="_head.tpl" title="Commande n°%s — %s"|args:$order.id:$order.person plugin_css=["style.css"]}

{include file="./_menu.tpl" current="home" current_sub="orders"}

{form_errors}

<h2 class="ruler">Synchronisation</h2>

{if !$is_synced}
<form method="post" action="">
	<p class="actions-center">
		{csrf_field key=$csrf_key}
		{button shape="reload" label="Tout synchroniser" name="sync_all" value=1 type="submit" class="main"}
	</p>
</form>
{/if}

<section class="sync">
	{if $f.create_payer_user !== $ha::NO_USER_ACTION}
	<article class="{if $order.id_user}ok{else}missing{/if}">
		<h3>Membre lié (paiement)</h3>
		{if $order.id_user}
		<p>
			{link href="!users/details.php?id=%d"|args:$order.id_user label=$order->getLinkedUserName()}
		</p>
		{elseif $found_user}
			<p class="block confirm">
				Membre correspondant trouvé&nbsp;:<br />
				<a href="{$admin_url}users/details.php?id={$found_user.id}">{$found_user.identity}</a>
			</p>
			<p class="actions">
				{linkbutton shape="link" label="Lier la commande à ce membre" href="?id=%d&set_user_id=%d"|args:$order.id:$found_user.id}
			</p>
		{else}
			<p class="alert block">
				Aucun membre correspondant n'a été trouvé.
			</p>
			<form method="post" action="{$admin_url}users/new.php?redirect={$self_url|cat:"&set_user_id=%d"|escape:'url'}">
				<p class="actions">
					{foreach from=$mapped_user key="key" item="value"}
					<input type="hidden" name="{$key}" value="{$value}" />
					{/foreach}
					{button type="submit" shape="plus" label="Créer un membre"}
				</p>
			</form>
		{/if}
	</article>
	{/if}

	<article class="{if $order.id_transaction}ok{else}missing{/if}">
		<h3>Écriture comptable</h3>
		{if $order.status === $order::STATUS_WAITING}
			<p class="alert block"><strong>Paiement incomplet</strong><br />Il n'est pas possible de créer une écriture pour cette commande.</p>
		{elseif $order.id_transaction}
			<p class="status">
				{link class="num" href="!acc/transactions/details.php?id=%d"|args:$order.id_transaction label="#%d"|args:$order.id_transaction}
			</p>
		{else}
			<form method="post" action="">
			<p class="actions">
				{csrf_field key=$csrf_key}
				{button shape="plus" label="Créer l'écriture comptable" name="create_transaction" value=1 type="submit"}
			</p>
			</form>
		{/if}
	</article>

	{if $f.type === 'Membership'}
		<article class="{if $has_all_users}ok{else}missing{/if}">
			<h3>Adhésions</h3>
			{if $has_all_users}
				<p class="alert">Toutes les adhésions sont liées à des membres.</p>
			{else}
				<p class="alert block">Certaines adhésions ne sont pas liées à des membres.</p>
				<form method="post" action="">
				<p class="actions">
					{csrf_field key=$csrf_key}
					{button shape="link" label="Créer ou lier les membres" name="create_users" value=1 type="submit"}
				</p>
				</form>
			{/if}
		</article>

		<article class="{if $has_all_subscriptions && $has_all_users}ok{else}missing{/if}">
			<h3>Inscriptions aux activités</h3>
			{if !$has_all_users}
				<p class="alert block">Certaines adhésions ne sont pas liées à des membres.</p>
			{elseif $has_all_subscriptions}
				<p class="status">Toutes les inscriptions ont été effectuées.</p>
			{else}
				<p class="status">Il manque des inscriptions.</p>
				<form method="post" action="">
				<p class="actions">
					{csrf_field key=$csrf_key}
					{button shape="link" label="Créer les inscriptions manquantes" name="create_subscriptions" value=1 type="submit"}
				</p>
				</form>
			{/if}
		</article>
	{/if}
</section>

<h2 class="ruler">Informations de la commande</h2>

<dl class="describe">
	<dt>Personne</dt>
	<dd>{$order.person}</dd>
	<dt>Numéro de commande</dt>
	<dd>{$order.id}</dd>
	<dt>Montant total</dt>
	<dd>{$order.amount|money_currency|raw}</dd>
	<dt>Date</dt>
	<dd>{$order.date|date}</dd>
	<dt>Statut</dt>
	<dd>{if $order.status}Payée{else}Paiement incomplet{/if}</dd>
</dl>

{if $items->count()}
	<h2 class="ruler">Articles de la commande</h2>
	{include file="%s/templates/_items_list.tpl"|args:$plugin_root list=$items details=false}
{/if}

{if $payments->count()}
	<h2 class="ruler">Paiements</h2>
	{include file="%s/templates/_payments_list.tpl"|args:$plugin_root list=$payments details=false}
{/if}

<h2 class="ruler">Personne ayant effectué le paiement</h2>

<dl class="describe">
	{foreach from=$payer_infos key="key" item="value"}
	<dt>{$key}</dt>
	<dd>
		{if $value instanceof \DateTime}
			{$value|date:'d/m/Y'}
		{else}
			{$value}
		{/if}
	</dd>
	{/foreach}
</dl>

<form method="post" action="" class="hidden" id="link_form">
	{csrf_field key=$csrf_key}
	<input type="hidden" name="id_item" />
	<input type="hidden" name="link_id_user" />
</form>

<script type="text/javascript">
var selector_url = {"!users/selector.php?_dialog"|local_url|escape:'json'};
{literal}
var f = $('#link_form');
window.openUserSelectorForItem = function(id) {
	f.id_item.value = id;
	g.openFrameDialog(selector_url);
}

window.g.inputListSelected = function(id_user, label) {
	g.closeDialog();
	f.link_id_user.value = id_user;
	f.submit();
}
{/literal}
</script>


{include file="_foot.tpl"}
