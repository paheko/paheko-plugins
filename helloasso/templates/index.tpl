{include file="_head.tpl" title="HelloAsso" current="plugin_%s"|args:$plugin.id}

{include file="%s/templates/_menu.tpl"|args:$plugin_root current="home" current_sub=null}

<table class="list">
	<thead>
		<tr>
			<td>Organisme</td>
			<th>Formulaire</th>
			<td>Type</td>
			<td>Statut</td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$list item="form"}
		<tr{if $form.state == 'Disabled'} class="disabled"{/if}>
			<td>{$form.org_name}</td>
			<th><a href="orders.php?id={$form.id}">{$form.name}</a></th>
			<td>{$form.type_label}</td>
			<td>{$form.state_label}</td>
		</tr>
		{/foreach}
	</tbody>
</table>

{include file="_foot.tpl"}
