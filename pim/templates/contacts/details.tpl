{include file="_head.tpl" current="plugin_pim" hide_title=true plugin_css=['calendar.css']}

{if !$dialog}
	{include file="./_nav.tpl" archived=null}
{/if}

{form_errors}

<p class="actions">
	{linkbutton href="edit.php?id=%d"|args:$contact.id label="Modifier" shape="edit" target="_dialog"}
	{linkbutton href="delete.php?id=%d"|args:$contact.id label="Supprimer" shape="delete" target="_dialog"}
	<br />

	<small>Fiche mise à jour :
	{$contact.updated|date_short:true}</small>
</p>

<dl class="describe">
	<dt>Identité</dt>
	<dd>{$title} {if $contact.context} ({$contact.context}){/if}</dd>

	{if $contact->hasPhoto()}
		<dt>Photo</dt>
		<dd><img src="{$contact->getPhotoURL()}" class="photo" alt="" /></dd>
	{/if}

	{if $contact.mobile_phone}
		<dt>Téléphone mobile</dt>
		<dd><a href="tel:{$contact.mobile_phone}">{$contact.mobile_phone|format_phone_number}</a></dd>
	{/if}

	{if $contact.phone}
		<dt>Téléphone fixe</dt>
		<dd><a href="tel:{$contact.phone}">{$contact.phone|format_phone_number}</a></dd>
	{/if}

	{if $contact.address}
		<dt>Adresse</dt>
		<dd>
			{$contact.address|escape|nl2br}<br />
			{linkbutton shape="globe" label="Montrer sur la carte" target="_blank" href=$contact->getMapURL()}
		</dd>
	{/if}

	{if $contact.email}
		<dt>E-Mail</dt>
		<dd><a href="mailto:{$contact.email|escape:'url'}">{$contact.email}</a></dd>
	{/if}

	{if $contact.web}
		<dt>Site web</dt>
		<dd><a href="{$contact.web}" target="_blank">{$contact.web}</a></dd>
	{/if}

	{if $contact.notes}
		<dt>Notes</dt>
		<dd>{$contact.notes|escape|nl2br}</dd>
	{/if}

	{if $contact.birthday}
		<dt>Âge</dt>
		<dd>{$contact->getAge()} ans</dd>
		<dt>Anniversaire</dt>
		<dd>{$contact.birthday|date_format:'%e %B'}</dd>
	{/if}
</dl>

<div style="clear:both"></div>

{include file="_foot.tpl"}
