{include file="_head.tpl" title="Synchroniser avec la comptabilité"}

{include file="./_nav.tpl" current='sync'}

{if isset($_GET['ok'])}
<p class="block confirm">
	{if !$_GET.ok}Aucune écriture n'avait besoin d'être ajoutée.{else}{$_GET.ok} écritures ont été ajoutées.{/if}
	{linkbutton href="!acc/search.php?qt=POS-SESSION-&year=%d"|args:$year.id label="Voir les écritures" shape="menu"}
</p>

{foreach from=$errors item="line"}
<p class="alert block">
	{if $line.debit}
		Un paiement de {$line.debit|money_currency|raw} sur la <a href="../session.php?id={$line.sid}">session n°{$line.sid}</a> n'a pas de compte associé.
	{else}
		Une recette de {$line.credit|money_currency|raw} sur la <a href="../session.php?id={$line.sid}">session n°{$line.sid}</a> n'a pas de compte associé.
	{/if}
	<br />
	Normalement si cette erreur survient c'est qu'une catégorie de produit ou un moyen de paiement a été configuré sans compte associé.
	Dans ce cas le montant a été comptabilisé comme une erreur de caisse.<br />
	{linkbutton href="!acc/search.php?qt=POS-SESSION-%d&year=%d"|args:$line.sid,$year.id label="Voir l'écriture" shape="search"}
</p>
{/foreach}
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

{include file="_foot.tpl"}