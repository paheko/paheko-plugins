{include file="_head.tpl" title=$plugin.label}

<nav class="tabs">
	<ul>
		<li><a href="index.php">Accueil</a></li>
		<li class="current"><a>Configuration</a></li>
	</ul>
</nav>

<form method="post" action="">

	<fieldset>
		<legend>Connexion à l'API HelloAsso</legend>
		<dl>
			{* input type="text" name="org_slug" label="URL" default=$org_slug required=true *}
			{input type="text" name="client_id" label="Client ID" default=$client_id required=true}
			{if $client_id==null}{assign var="required" value=true}{else}{assign var="required" value=false}{/if}
			{input type="password" name="client_secret" label="Client Secret" required=$required}
		</dl>
	</fieldset>

	<fieldset>
		<legend>Paramètres</legend>
		<dl>
			{input required=true name="account" multiple=false target="!acc/charts/accounts/selector.php?targets=1:2:3&chart=1" type="list" label="Compte HelloAsso" default=$account help="Compte correspondant au solde HelloAsso en attente (versé mensuellement sur votre compte courant)."}
			{input type="checkbox" name="sandbox" value=true label="Mode sandbox" default=$sandbox help="Permet d'utiliser api.helloasso-sandbox.com (au lieu de api.helloasso.com) pour tester les paiements pour de faux"}
		</dl>
	</fieldset>

	{if $error}<p class="error block">{$error}</p>{/if}

	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
	</p>

</form>

{include file="_foot.tpl"}