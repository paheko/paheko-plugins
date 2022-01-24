{include file="admin/_head.tpl" title="Synchroniser avec la comptabilité" current="plugin_%s"|args:$plugin.id}

{include file="%s/manage/_nav.tpl"|args:$pos_templates_root current='sync'}

{if isset($_GET['ok'])}
<p class="block confirm">
	{if !$_GET.ok}Aucune écriture n'avait besoin d'être ajoutée.{else}{$_GET.ok} écritures ont été ajoutées.{/if}
	{linkbutton href="!acc/search.php?qt=POS-SESSION-&year=%d"|args:$year.id label="Voir les écritures" shape="menu"}
</p>
{/if}

{form_errors}

<form method="post" action="{$self_url}">
	<fieldset>
		<legend>Synchroniser</legend>
		<dl>
			{input type="select" label="Exercice vers lequel synchroniser la caisse" name="year" required=true help="Seules les sessions de caisse dont la date correspond à l'exercice sélectionné seront synchronisées." options=$years}
		</dl>
	</fieldset>
	<p class="help">
		Cette action créera une écriture pour chaque session de caisse, avec en fichier joint une copie de la session de caisse.<br />
		<strong>Attention</strong>, pour éviter les écritures en double il est conseillé de ne pas modifier le numéro de pièce comptable de ces écritures.
	</p>
	<p class="submit">
		{button name="sync" label="Synchroniser" shape="right" type="submit" class="main"}
	</p>
</form>

{include file="admin/_foot.tpl"}