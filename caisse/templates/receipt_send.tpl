{include file="_head.tpl" title="Envoyer un reçu"}

{if $_GET.send === 'done'}
	<p class="block confirm">Le reçu a bien été envoyé par e-mail.</p>
{/if}

{form_errors}

<form method="post" action="">
	<fieldset>
		<legend>Envoyer un reçu</legend>
		<dl>
			{input type="text" name="name" required=true default=$name label="Nom de la personne à inscrire sur le reçu"}
			{input type="email" name="to" required=true default=$email label="Adresse du destinataire"}
			{input name="subject" type="text" default="Votre reçu" label="Sujet du message" required=true}
			{input name="body" type="textarea" cols="70" rows=7 default="Veuillez trouver ci-joint votre reçu au format PDF." label="Corps du message" required=true}
		</dl>
	</fieldset>
	<p class="submit">
		{button type="submit" label="Envoyer" name="send" shape="right" class="main"}
		{csrf_field key=$csrf_key}
	</p>
</form>

{include file="_foot.tpl"}
