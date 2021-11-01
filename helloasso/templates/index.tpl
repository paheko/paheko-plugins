{include file="admin/_head.tpl" title="HelloAsso" current="plugin_%s"|args:$plugin.id}

{include file="%s/templates/_menu.tpl"|args:$plugin_root current="home" show_reset_button=true}

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
		<tr{if $form.status == 'désactivé'} class="disabled"{/if}>
			<td>{$form.org_name}</td>
			<th><a href="form.php?id={$form.id}">{$form.name}</a></th>
			<td>{$form.type}</td>
			<td>{$form.status}</td>
		</tr>
		{/foreach}
	</tbody>
</table>

{include file="admin/_foot.tpl"}
