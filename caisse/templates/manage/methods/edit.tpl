{include file="admin/_head.tpl" title="Gestion moyen de paiement" current="plugin_%s"|args:$plugin.id}

{form_errors}

<form method="post" action="{$self_url}" data-focus="1">
	<fieldset>
		<legend>Modifier un moyen de paiement</legend>
		<dl>
			{input type="text" name="name" label="Nom" required=true source=$method}
			{input type="text" name="account" label="Code du compte" source=$method help="Code du compte dans le plan comptable (par exemple 530  pour les espèces), utilisé pour intégrer les notes à la comptabilité."}
			{input type="money" name="min" label="Minimum" source=$method help="Si renseigné, ce moyen de paiement ne pourra pas être utilisé pour un paiement inférieur à ce montant."}
			{input type="money" name="max" label="Maximum" source=$method help="Si renseigné, ce moyen de paiement ne pourra pas être utilisé pour un paiement supérieur à ce montant."}
			{input type="checkbox" name="is_cash" value=1 label="Ne pas demander de référence de paiement" source=$method help="Si décoché, une référence sera demandée pour chaque paiement avec ce moyen (par exemple : numéro de chèque), et il faudra valider chaque paiement lors de la clôture de la caisse (pour vérifier que le paiement n'a pas été égaré)."}
			{input type="checkbox" name="enabled" value=1 label="Activer ce moyen de paiement" source=$method help="Si décoché, ce moyen de paiement ne sera pas utilisable dans la caisse."}
		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
	</p>
</form>

{include file="admin/_foot.tpl"}