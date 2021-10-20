{include file="admin/_head.tpl" title="Connexion à HelloAsso" current="plugin_%s"|args:$plugin.id}

{include file="%s/templates/_menu.tpl"|args:$plugin_root current="config_client"}

{if isset($_GET['ok'])}
<p class="confirm block">
	Connexion à l'API HelloAsso effectuée !
</p>
{/if}

{form_errors}

<div class="help block">
	<p>Cette extension permet d'importer les données des personnes ayant effectué un règlement à votre association sur la plateforme HelloAsso : création de membre, inscription à une cotisation ou activité, et enregistrement en comptabilité.</p>
	<p>Attention HelloAsso ne permet pas d'importer la liste des membres que vous avez rentré sur leur site&nbsp;! Pour cela il est conseillé de faire un export en CSV depuis leur site et de l'importer ensuite sur Garradin. Ceci est une limitation de HelloAsso.</p>
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
			{input type="password" name="client_secret" value="1" default=$secret label="Secret (Mon clientSecret)" required=true}
		</dl>
	</fieldset>

	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" class="main" name="save" label="Enregistrer" shape="right"}
	</p>
</form>

<h3>Identifiants de connexion temporaire (debug)</h3>
<p><textarea readonly="readonly" cols="70" rows="10" onclick="this.select();">{$oauth}</textarea></p>

{include file="admin/_foot.tpl"}
