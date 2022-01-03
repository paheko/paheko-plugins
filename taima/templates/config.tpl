{include file="admin/_head.tpl" title="Configuration des tâches" plugin_css=['style.css']}

{include file="%s/templates/_nav.tpl"|args:$plugin_root current="config"}

{form_errors}

<table class="list">
	<thead>
		<tr>
			<th>Libellé</th>
			<td class="num">Compte de valorisation</td>
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
				{linkbutton label="Éditer" href="?edit=%d"|args:$task.id shape="edit" target="_dialog"}
				{linkbutton label="Supprimer" href="?delete=%d"|args:$task.id shape="delete" target="_dialog"}
			</td>
		</tr>
	{/foreach}
	</tbody>
</table>

<form method="post" action="">
	<fieldset>
		<legend>Ajouter une tâche</legend>
		<dl>
			{input type="text" name="label" required=true label="Libellé"}
			{input type="list" target="!acc/charts/accounts/selector.php?targets=%s"|args:$account_targets name="account" label="Compte de valorisation" required=false help="Compte qui sera utilisé pour reporter le temps bénévole dans le bilan comptable"}
			{input type="money" name="value" required=false label="Valorisation d'une heure" help="Inscrire ici la valeur d'une heure de temps pour le bilan comptable"}
			<dd class="help">On utilise ici généralement le SMIC avec les charges, environ 12 €, et on multiplie selon le niveau de responsabilité&nbsp;: x3 pour un niveau cadre, x5 pour une fonction de direction, etc.</dd>
		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="add" label="Ajouter cette tâche à la liste" shape="plus" class="main"}
	</p>
</form>


{include file="admin/_foot.tpl"}