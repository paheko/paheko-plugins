{include file="_head.tpl" title="HelloAsso"}

{include file="./_menu.tpl" current="home" current_sub=null}

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
			<td>{tag color=$form.state_color label=$form.state_label}</td>
		</tr>
		{/foreach}
	</tbody>
</table>

{include file="_foot.tpl"}
