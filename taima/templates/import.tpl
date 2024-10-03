{include file="_head.tpl" title="Import de tâches"}

{include file="./_nav.tpl"}
{form_errors}

{if $_GET.msg === 'OK'}
	<p class="block confirm">L'import est terminé.</p>
{/if}


<form method="post" action="" enctype="multipart/form-data">
{if $csv->ready() && $categories === null}
	<table class="list">
		<thead>
			<tr>
				<td class="num">Ligne</td>
				<th>Catégorie</th>
				<td>Date</td>
				<td>Durée</td>
				<td>Membre</td>
				<td>Description</td>
			</tr>
		</thead>
		<tbody>
			{foreach from=$rows key="i" item="row"}
			<tr>
				<td class="num">{$i}</td>
				<th>
					{if $row.task_id}
						{$tasks[$row.task_id]}
					{else}
						— Non spécifié —
					{/if}
				</th>
				<td>
					{$row.date|date_short}
				</td>
				<td>
					{$row.duration|taima_minutes}
				</td>
				<td>
					{if !$row.user_id}
						{tag color="darkred" label="Non trouvé"}
					{else}
						<?php $users_names[$row->user_id] ??= $row->user_name(); ?>
						{$users_names[$row.user_id]}
					{/if}
				</td>
				<td>
					{$row.notes|escape|nl2br}
				</td>
			</tr>
			{/foreach}
		</tbody>
	</table>

	<p class="submit">
		{foreach from=$categories_match key="label" item="id"}
			{input type="hidden" name="categories[%s]"|args:$label default=$id}
		{/foreach}
		{csrf_field key=$csrf_key}
		{linkbutton href="?" label="Retour" shape="left"}
		{button type="submit" name="import" label="Terminer l'import" shape="right" class="main"}
	</p>

{elseif $csv->ready()}
	<fieldset>
		<legend>Correspondance des catégories</legend>
		<table class="list auto">
			<thead>
				<tr>
					<td>Catégorie du fichier</td>
					<td>Catégorie existante</td>
				</tr>
			<tbody>
				{foreach from=$categories key="label" item="match"}
				<tr>
					<td>{$label}</td>
					<td>{input type="select" name="categories[%s]"|args:$label options=$tasks default=$match default_empty="— Non spécifiée —"}</td>
				</tr>
				{/foreach}
			</tbody>
		</table>
	</fieldset>

	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="cancel" value="1" label="Annuler" shape="left"}
		{button type="submit" name="preview" label="Prévisualiser" shape="right" class="main"}
	</p>
{elseif $csv->loaded()}

	{include file="common/_csv_match_columns.tpl"}

	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="cancel" value="1" label="Annuler" shape="left"}
		{button type="submit" name="set_translation_table" label="Prévisualiser" shape="right" class="main"}
	</p>

{else}
	<details class="help block">
		<summary><h3>Importer depuis Bénévalibre</h3></summary>
		<p>Cet outil permet d'importer également les données depuis Bénévalibre. {linkbutton href="https://app.benevalibre.org/" target="_blank" label="Application Bénévalibre"}</p>
		<p>Pour récupérer le fichier depuis l'application Bénévalibre&nbsp;:</p>
		<ul>
			<li>cliquer sur <strong>Liste des bénévolats</strong></li>
			<li>sur la page listant les bénévolats, cliquer sur le bouton en haut <strong>Exporter les données</strong></li>
			<li>Sélectionner le <strong>Format CSV</strong></li>
		</ul>
		<p>Importer ensuite ce fichier ici, l'outil détectera automatiquement le format de Bénévalibre.</p>
	</details>

	<fieldset>
		<legend>Importer depuis un fichier</legend>
		<dl>
			{input type="file" name="file" label="Fichier à importer" required=true accept="csv"}
			{include file="common/_csv_help.tpl" csv=$csv}
		</dl>
	</fieldset>

	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="load" label="Charger le fichier" shape="right" class="main"}
	</p>
</form>
{/if}

{include file="_foot.tpl"}