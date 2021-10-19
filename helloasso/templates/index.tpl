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

<p>
	Pour aider au développement de cette extension, vous pouvez cliquer sur le bouton suivant, qui procédera à l'envoi d'une copie de vos derniers paiements HelloAsso, cela enverra au développeur une copie de la liste des valeurs disponibles dans HelloAsso (sans les valeurs elle-même, donc aucune information personnelle ne sera envoyée).
</p>

<p>
	{linkbutton shape="upload" label="Envoyer les valeurs possibles" href="?send_debug"}
</p>

{include file="admin/_foot.tpl"}
