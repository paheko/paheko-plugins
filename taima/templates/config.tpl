{include file="_head.tpl" title="Configuration"}

{if !$dialog}
	{include file="./_nav.tpl" current="config"}
{/if}

{form_errors}

<table class="list">
	<thead>
		<tr>
			<th>Libellé</th>
			<td class="num">Compte d'emploi</td>
			<td class="money">Valorisation horaire</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
	{foreach from=$tasks item="task"}
		<tr>
			<th>{$task.label}</th>
			<td class="num">{$task.account}</td>
			<td class="money">{if $task.value}{$task.value|raw|money_currency:true} / h{/if}</td>
			<td class="actions">
				{linkbutton label="Suivi des tâches" href="all.php?id_task=%d"|args:$task.id shape="menu"}
				{linkbutton label="Éditer" href="?edit=%d"|args:$task.id shape="edit" target="_dialog"}
				{linkbutton label="Supprimer" href="?delete=%d"|args:$task.id shape="delete" target="_dialog"}
			</td>
		</tr>
	{/foreach}
	</tbody>
</table>

<form method="post" action="">
	<fieldset>
		<legend>Ajouter une catégorie</legend>
		<dl>
			{input type="text" name="label" required=true label="Libellé"}
			{input type="list" target="!acc/charts/accounts/selector.php?types=%s&key=code"|args:$account_types name="account" label="Code du compte d'emploi" required=false help="Compte qui sera utilisé pour reporter l'utilisation du temps bénévole dans le bilan comptable. Généralement c'est le compte 864." default=864}
			{input type="money" name="value" required=false label="Valorisation d'une heure" help="Inscrire ici la valeur d'une heure de temps pour le bilan comptable"}
			<dd class="help">On utilise ici généralement le SMIC avec les charges, environ 13 €, et on multiplie selon le niveau de responsabilité&nbsp;: x3 pour un niveau cadre, x5 pour une fonction de direction, etc.</dd>
		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="add" label="Ajouter cette catégorie" shape="plus" class="main"}
	</p>
</form>


{include file="_foot.tpl"}