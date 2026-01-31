{if $type}
	{assign var="title" value="HelloAsso"}
{else}
	{assign var="title" value="HelloAsso — Toutes les campagnes"}
{/if}
{include file="_head.tpl" title=$title}

{include file="./_menu.tpl" current="home" current_sub=null}

<table class="list">
	<thead>
		<tr>
			<td>Organisme</td>
			<th>Formulaire</th>
			<td>Type</td>
			<td>Statut</td>
			{if $session->canAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN)}
			<td class="actions"></td>
			{/if}
		</tr>
	</thead>
	<tbody>
		{foreach from=$list item="item"}
		<tr{if $item.state === 'Disabled'} class="disabled"{/if}>
			<td>{$item.org_name}</td>
			<th><a href="orders.php?id={$item.id}">{$item.name}</a></th>
			<td>{$item.type_label}</td>
			<td>{tag color=$item.state_color label=$item.state_label}</td>
			{if $session->canAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN)}
			<td class="actions">
				{if $item.type === 'Membership'}
					{linkbutton href="tiers.php?id=%d"|args:$item.id label="Tarifs" shape="menu" target="_dialog"}
					{linkbutton href="form.php?id=%d"|args:$item.id label="Configurer" shape="settings" target="_dialog"}
				{/if}
			</td>
			{/if}
		</tr>
		{/foreach}
	</tbody>
</table>

{include file="_foot.tpl"}
