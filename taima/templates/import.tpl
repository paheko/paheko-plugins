{include file="_head.tpl" title="Import Bénévalibre"}

{include file="./_nav.tpl" current="config"}
{form_errors}

{if isset($_GET['ok'])}
<p class="block confirm">L'import est terminé.</p>
{/if}

{if !empty($add)}
<form method="post" action="">

	<table class="list">
		<thead>
			<tr>
				<td class="num">Ligne</td>
				<th>Tâche</th>
				<td>Date</td>
				<td>Durée</td>
				<td>Membre</td>
				<td>Description</td>
				<td class="actions"></td>
			</tr>
		</thead>
		<tbody>
			{foreach from=$add key="l" item="row"}
			<tr>
				<td class="num"><?=$l+1?></td>
				<th>
					<?=htmlspecialchars($tasks[$row['entry']->task_id]??'--')?>
					<input type="hidden" name="entries[task_id][]" value="{$row.entry.task_id}" />
				</th>
				<td>
					{$row.entry.date|date_short}
					<input type="hidden" name="entries[date][]" value="{$row.entry.date|date_short}" />
				</td>
				<td>
					{$row.entry.duration|taima_minutes}
					<input type="hidden" name="entries[duration][]" value="{$row.entry.duration}" />
				</td>
				<td>
					{if !$row.entry.user_id}<span class="error">Non trouvé :</span>{/if}
					<?=htmlspecialchars(($row['Nom'] ?? '') . ' ' . ($row['Prénom'] ?? ''))?>
					<input type="hidden" name="entries[user_id][]" value="{$row.entry.user_id}" />
				</td>
				<td>
					{$row.entry.notes|escape|nl2br}
					<input type="hidden" name="entries[notes][]" value="{$row.entry.notes}" />
				</td>
				<td class="actions">
					{button shape="delete" label="Supprimer cette ligne" onclick="this.parentNode.parentNode.remove();"}
				</td>
			</tr>
			{/foreach}
		</tbody>
	</table>

	<p class="submit">
		{csrf_field key=$csrf_key}
		{linkbutton href="?cancel" label="Annuler" shape="left"}
		{button type="submit" name="save" label="Terminer l'import" shape="right" class="main"}
	</p>
</form>
{elseif !empty($links)}
<form method="post" action="">

	<fieldset>
		<legend>Associer les tâches aux catégories, projets et niveaux</legend>
		<table class="list auto">
			{foreach from=$links key="item" item="task"}
			<tr>
				<th>{$item}</th>
				<td>{input type="select" name="links[%s]"|args:$item options=$tasks default=$task}</td>
			</tr>
			{/foreach}
		</table>
	</fieldset>

	<p class="submit">
		{csrf_field key=$csrf_key}
		{linkbutton href="?cancel" label="Annuler" shape="left"}
		{button type="submit" name="preview" label="Prévisualiser" shape="right" class="main"}
	</p>
</form>
{else}
<p class="help">
	L'import tentera de faire le rapprochement entre la catégorie, le niveau ou le projet indiqué dans Bénévalibre et les tâches de Tāima. De même pour les membres. Si aucun membre n'existe avec le nom fourni dans l'export Bénévalibre, l'entrée n'aura pas de membre associé.
</p>

<form method="post" action="" enctype="multipart/form-data">
	<fieldset>
		<legend>Importer depuis Bénévalibre</legend>
		<dl>
			{input type="file" name="json" required=true label="Fichier export au format JSON de Bénévalibre" help="Dans Bénévalibre, cliquer sur le menu 'Exporter les données' et choisir 'Format JSON'."}
		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="load" label="Prévisualiser" shape="right" class="main"}
	</p>
</form>
{/if}

{include file="_foot.tpl"}