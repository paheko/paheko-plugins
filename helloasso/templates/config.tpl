{include file="admin/_head.tpl" title="Configuration" current="plugin_%s"|args:$plugin.id}

{include file="%s/templates/_menu.tpl"|args:$plugin_root current="config"}

{if $_GET.ok !== null}
<p class="confirm block">
	Configuration enregistrée.
</p>
{/if}


{form_errors}

<form method="post" action="{$self_url}">
	<fieldset>
		<legend>Correspondance avec les fiches de membres</legend>
		<table class="list auto">
			<thead>
				<tr>
					<th>Donnée HelloAsso</th>
					<th>Importer cette donnée comme…</th>
				</tr>
			</thead>
			<tbody>
			{foreach from=$plugin_config.map_user_fields key="source" item="target"}
				<tr>
					<th><?=$fields_names[$source]?></th>
					<td>
						{input type="select" options=$target_fields name="map[%s]"|args:$source default=$target}
					</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
		<p class="help">Pour fusionner prénom et nom dans le même champ de la fiche membre, sélectionner le même champ cible pour les données <em>Prénom</em> et <em>Nom</em>.</p>
		<dl class="merge_names">
			{input type="select" options=$merge_names_options name="merge_names" source=$plugin_config required=true label="Ordre des noms"}
		</dl>
		<dl>
			{input type="select" options=$match_options name="match_email_field" source=$plugin_config required=true label="Champ utilisé pour savoir si un membre existe déjà"}
		</dl>
	</fieldset>


	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" class="main" name="save" label="Enregistrer" shape="right"}
	</p>
</form>

<script type="text/javascript">
{literal}
function toggleMergeOption()
{
	let enabled = $('#f_mapfirstName').value == $('#f_maplastName').value;
	g.toggle('.merge_names', enabled);
}

$('#f_mapfirstName').onchange = toggleMergeOption;
$('#f_maplastName').onchange = toggleMergeOption;
toggleMergeOption();
{/literal}
</script>

{include file="admin/_foot.tpl"}
