{include file="admin/_head.tpl" title="Export compta" current="plugin_%s"|args:$plugin.id}

<form method="post" action="{$self_url}">
	<fieldset>
		<legend>Intervalle d'export</legend>
		<dl>
			{input type="date" label="Date de début" name="start" required=true default=$start}
			{input type="date" label="Date de fin" name="end" required=true default=$end}
		</dl>
	</fieldset>
	<p class="submit">
		{button name="export" label="Créer un export CSV correspondant à ces dates" shape="right" type="submit" class="main"}
	</p>
</form>

{include file="admin/_foot.tpl"}