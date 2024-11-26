{include file="_head.tpl" title="Suivi du temps"}

{form_errors}

<form method="post" action="" data-focus="1">
	<fieldset>
		<legend>Modifier une catégorie</legend>
		<dl>
			{input type="text" name="label" required=true label="Libellé" source=$task}
			{input type="text" name="account" label="Code du compte d'emploi" required=false help="Compte qui sera utilisé pour reporter l'utilisation du temps bénévole dans le bilan comptable. Généralement c'est le compte 864." source=$task}
			{input type="money" name="value" required=false label="Valorisation d'une heure" help="Inscrire ici la valeur d'une heure de temps pour le bilan comptable" source=$task}
			{if count($projects)}
				{input type="select" name="id_project" required=false label="Projet analytique" source=$task options=$projects default_empty="— Aucun —"}
			{/if}
		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="edit" label="Modifier" shape="edit" class="main"}
	</p>
</form>


{include file="_foot.tpl"}