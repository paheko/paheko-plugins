{include file="_head.tpl" title="Connexion à HelloAsso"}

{include file="./_menu.tpl" current="config" sub_current="client"}

{form_errors}

<div class="help block">
	<p>Cette extension permet d'importer les données des personnes ayant effectué un règlement à votre association sur la plateforme HelloAsso : création de membre, inscription à une cotisation ou activité, et enregistrement en comptabilité.</p>
	<p>Cette extension est accessible aux membres qui ont le droit de modifier les membres.</p>
</div>

<form method="post" action="{$self_url}">
	<fieldset>
		<legend>Connexion à HelloAsso</legend>
		<p class="help">
			Pour renseigner ces informations, rendez-vous dans <a href="https://admin.helloasso.com/" target="_blank">votre administration HelloAsso</a> et allez dans <em>«&nbsp;Mon compte&nbsp;»</em>, puis <em>«&nbsp;Intégrations et API&nbsp;»</em> et recopiez ici les valeurs indiquées.
		</p>
		<dl>
			{input type="text" name="client_id" default=$client_id label="ID (Mon clientId)" required=true}
			{input type="password" name="client_secret" value="1" label="Secret (Mon clientSecret)" required=true}
			{input type="checkbox" name="sandbox" value=1 label="Utiliser l'environnement de test (sandbox)" default=$sandbox}
			<dd class="help">Note : les comptes de l'environnement de test sont complètement différents, il faut donc <a href="https://www.helloasso-sandbox.com" target="_blank">se re-créer un compte sur helloasso-sandbox.com</a>.</dd>
		</dl>
	</fieldset>

	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" class="main" name="save" label="Enregistrer" shape="right"}
	</p>
</form>

{include file="_foot.tpl"}
