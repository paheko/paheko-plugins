{include file="_head.tpl" title="Notifications — Configuration"}

{form_errors}

{if isset($_GET['ok']) && !$form->hasErrors()}
	<p class="confirm block">La configuration a été enregistrée.</p>
{/if}

<table class="list">
	<thead>
		<tr>
			<td>Événement</td>
			<td>Action</td>
			<td>Détails</td>
			<td class="actions"></td>
		</tr>
	</thead>
	<tbody>
	{foreach from=$notifications key="idx" item="n"}
		<tr>
			<th>{$n.signal_label}</th>
			<td>{$n.action_label}</td>
			<td>
				{$n.config.email}
				{if $n.file_context}
				<br/>Contexte : {$n.file_context}
				{/if}
			</td>
			<td class="actions">
				{linkbutton shape="delete" href="?delete=%d"|args:$idx label="Supprimer"}
			</td>
		</tr>
	{/foreach}
	</tbody>
</table>

<form method="post" action="{$self_url}">
	<fieldset>
		<legend>Ajouter une notification</legend>
		<dl>
			{input type="select" name="signal" label="Événement" options=$signals required=true onchange="g.toggle('.file-options', this.value.match(/^file\./));"}
			{input type="select" name="action" label="Action" options=$actions required=true}
			{input type="email" name="config[email]" label="Adresse e-mail destinataire" required=true}
		</dl>
		<dl class="file-options hidden">
			{input type="select" name="config[file_context]" label="Limiter aux fichiers de ce type" options=$file_contexts required=true}
		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button name="add" label="Ajouter" shape="right" type="submit" class="main"}
	</p>
</form>

{include file="_foot.tpl"}