{include file="_head.tpl"}

{form_errors}

<form method="post" action="">
	<fieldset>
		<legend>{$title}</legend>
		<dl>
			{if !$account->exists()}
				{input type="list" name="id_user" default=$account_user multiple=false label="Membre" required=true target="!users/selector.php"}
			{/if}
			{input type="email" name="address" source=$account label="Adresse e-mail" required=true}
			{if $account->exists()}
				{input type="password" name="password" label="Changer le mot de passe" help="Laisser vide si le mot de passe ne doit pas être modifié."}
			{else}
				{input type="password" name="password" label="Mot de passe" required=true}
			{/if}
		</dl>
	</fieldset>

	<fieldset>
		<legend>Serveur IMAP (réception)</legend>
		<dl>
			{input type="text" name="imap_server" source=$account required=true label="Adresse du serveur"}
			{input type="text" size=3 pattern="\d+" step=1 name="imap_port" source=$account required=true label="Port du serveur" default=$default_imap_port}
			{input type="select" name="imap_security" source=$account required=true label="Sécurité du serveur" default="tls" options=$security_options}
		</dl>
	</fieldset>

	<fieldset>
		<legend>Serveur SMTP (envoi)</legend>
		<dl>
			{input type="text" name="smtp_server" source=$account required=true label="Adresse du serveur"}
			{input type="text" size=3 pattern="\d+" step=1 name="smtp_port" source=$account required=true label="Port du serveur" default=$default_smtp_port}
			{input type="select" name="smtp_security" source=$account required=true label="Sécurité du serveur" default="tls" options=$security_options}
		</dl>
	</fieldset>

	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="save" label="Enregistrer" class="main" shape="right"}
	</p>
</form>


{include file="_foot.tpl"}