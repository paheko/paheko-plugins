{include file="admin/_head.tpl" title="Gestion de la caisse" current="plugin_%s"|args:$plugin.id}

{include file="%s/manage/_nav.tpl"|args:$pos_templates_root}

<form method="get" action="user_tabs.php">
	<fieldset>
		<legend>Rechercher les notes associées à un membre</legend>
		<dl>
			{input type="text" name="q" label="Numéro ou nom de membre" required=true}
		</dl>
	</fieldset>
	<p class="submit">
		{button type="submit" label="Chercher" shape="right"}
	</p>
</form>

{include file="admin/_foot.tpl"}