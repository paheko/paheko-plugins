{include file="_head.tpl" title="Synchronisation — %s"|args:$plugin.nom current="plugin_%s"|args:$plugin.id}

<nav class="tabs">
	<ul>
		<li><a href="config.php">Connexion à HelloAsso</a></li>
		<li class="current"><a href="targets.php">Synchronisation</a></li>
	</ul>
</nav>

<p class="help">
	Cette page permet de configurer la synchronisation entre les paiements HelloAsso et la création de membre et l'inscription aux activités.
</p>

{form_errors}

<table>
	<thead>
		<tr>
			<th>Formulaire</th>
			<td>Dernière synchronisation le</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$list item="row"}
		<tr>
			<th>{$row.label}</th>
			<td>{$row.last_sync|date}</td>
			<td>
				{linkbutton href="target_details.php" shape="print" label="Liste des synchronisations"}
				{linkbutton href="target_edit.php" shape="edit" label="Modifier"}
				{linkbutton href="target_delete.php" shape="delete" label="Supprimer"}
			</td>
		{/foreach}
	</tbody>
</table>

<form method="post" action="{$self_url}">
	<fieldset>
		<legend>Ajouter une nouvelle synchronisation</legend>
		<dl>
			{input type="select" name="form" label="Formulaire" required=true}
		</dl>
	</fieldset>

	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" class="main" name="save" label="Ajouter" shape="right"}
	</p>
</form>

{include file="_foot.tpl"}
