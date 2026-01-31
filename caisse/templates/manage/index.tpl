{include file="_head.tpl" title="Gestion de la caisse"}

{include file="./_nav.tpl"}

<form method="get" action="user_tabs.php">
	<fieldset>
		<legend>Rechercher les notes</legend>
		<dl>
			{input type="text" name="q" label="Numéro ou nom de membre, ou nom de la note" required=true}
		</dl>
	</fieldset>
	<p class="submit">
		{button type="submit" label="Chercher" shape="right"}
	</p>
</form>

{include file="_foot.tpl"}