{include file="admin/_head.tpl" title="Export compta" current="plugin_%s"|args:$plugin.id}

{include file="%s/manage/_nav.tpl"|args:$pos_templates_root current='export'}

<form method="post" action="{$self_url}">
	<fieldset>
		<legend>Intervalle d'export</legend>
		<dl>
			{input type="date" label="Date de début" name="start" required=true default=$start}
			{input type="date" label="Date de fin" name="end" required=true default=$end}
		</dl>
	</fieldset>
	<p class="help">
		Cet export peut ensuite {link href="!acc/years/import.php" label="être importé dans la comptabilité"} en sélectionnant le format «&nbsp;Complet groupé (comptabilité d'engagement)&nbsp;».
	</p>
	<p class="submit">
		{button name="export" label="Créer un export CSV correspondant à ces dates" shape="right" type="submit" class="main"}
	</p>
</form>

{include file="admin/_foot.tpl"}