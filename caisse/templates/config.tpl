{include file="_head.tpl" title="Caisse — Configuration"}

{include file="./manage/_nav.tpl" current="config"}

{form_errors}

{if isset($_GET['ok'])}
	<p class="confirm block">La configuration a été enregistrée.</p>
{/if}

<form method="post" action="{$self_url}">
	<fieldset>
		<legend>Configuration</legend>
		<dl>
			{input type="email" name="send_email_when_closing" label="Adresse e-mail où sera envoyé la note de caisse à la clôture" default=$plugin.config.send_email_when_closing help="Laisser vide pour ne pas envoyer d'e-mail"}
			{input type="checkbox" name="allow_custom_user_name" label="Permettre aux personnes gérant la caisse de définir leur nom à l'ouverture et à la clôture" default=$plugin.config.allow_custom_user_name value=1}
			<dd class="help">Si cette case est cochée, un champ texte à l'ouverture et à la clôture permettra de saisir son nom.<br />
				Si la case est décochée, c'est le nom du membre actuellement connecté qui sera enregistré.<br />
				Utiliser ce réglage si vous avez des bénévoles partageant le même compte membre.
			</dd>
			{input type="checkbox" name="auto_close_tabs" label="Clôturer automatiquement les notes de caisse une fois qu'elles sont entièrement réglées" default=$plugin.config.auto_close_tabs value=1}
			</dd>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button name="save" label="Enregistrer" shape="right" type="submit" class="main"}
	</p>
</form>

{include file="_foot.tpl"}