{if $channel->exists()}
	{assign var="title" value="Gérer un salon de discussion"}
{else}
	{assign var="title" value="Créer un nouveau salon de discussion"}
{/if}
{include file="_head.tpl" title=$title}
{form_errors}

<form method="post" action="" data-focus="1">
	<fieldset>
		<legend>{$title}</legend>
		<dl>
			{input type="text" label="Nom" required=true name="name" source=$channel maxlength=50}
			{input type="text" name="description" label="Description" source=$channel maxlength=200 size=100}
			<dt><label for="f_access_public">Accès</label></dt>
			{input type="radio-btn" name="access" value=$channel::ACCESS_PUBLIC label="Discussion publique" source=$channel help="Toute personne, interne ou externe à l'organisation, pourra participer, sans connexion."}
			{input type="radio-btn" name="access" value=$channel::ACCESS_PRIVATE label="Discussion privée" help="Seuls les membres connectés pourront accéder à cette discussion." source=$channel}
			{*
			{input type="radio-btn" name="access" value=$channel::ACCESS_INVITE label="Discussion privée, sur invitation uniquement" help="Seuls les membres et personnes externes invitées à rejoindre le salon pourront accéder à la discussion." source=$channel}
			*}
		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="save" label="Enregistrer" class="main" shape="right"}
	</p>
</form>


{include file="_foot.tpl"}