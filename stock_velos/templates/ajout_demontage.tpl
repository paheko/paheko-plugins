{include file="_head.tpl" title="Enregistrer vélos démontés"}

{include file="./_nav.tpl" current="ajout_demontage"}

<form method="post" action="{$self_url}">

{form_errors}

{*
<table class="list">
	<thead>
		<tr>
			<th>Nombre vélos</th>
			{foreach from=$fields item="field"}
				{if !$field.enabled}
				<?php continue; ?>
				{/if}
				<td>
					{$field.label}
					{if $field.required}
						<b>(o)</b>
					{/if}
				</td>
			{/foreach}
		</tr>
	</thead>
	<tbody>
		{foreach from=$entries item="entry"}
		<tr>
			<th>{input type="number" name="entry[]" required=true default=1}
		{/foreach}
	</tbody>
</table>
*}

<fieldset>
    <legend>Général</legend>
    <dl>
        <dd class="help">Ce formulaire permet d'enregistrer plusieurs vélos qui seront créés et directement marqués comme démontés.</dd>
        {input type="number" step="1" name="nb" label="Nombre de vélos à enregistrer" required=1}
        {input type="select" name="source" label="D'où provient le vélo ?" required=1 default="Partenariat" options=$fields.source.options}
        {input type="text" name="source_details" label="Détails sur la provenance"}
    </dl>
</fieldset>

<p class="submit">
    {csrf_field key="ajout_velos"}
    <input type="submit" name="save" value="Enregistrer &rarr;" />
</p>
</form>

{include file="_foot.tpl"}