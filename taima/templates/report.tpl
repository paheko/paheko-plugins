{include file="_head.tpl" title="Valorisation du temps bénévole"}

{include file="./_nav.tpl" current="report"}

{form_errors}


{if isset($_GET.ok)}
	<p class="block confirm">
		L'écriture numéro {link href="!acc/transactions/details.php?id=%d"|args:$_GET.ok class="num" label="#%d"|args:$_GET.ok} a été ajoutée.
	</p>
{/if}


{if empty($year)}
	<form method="post" action="">
		<fieldset>
			<legend>Exercice</legend>
			<dl>
				{input type="select" name="id_year" options=$years required=true label="Exercice où reporter la valorisation"}
			</dl>
		</fieldset>

		<p class="submit">
			{csrf_field key=$csrf_key}
			{button type="submit" name="next" label="Continuer" shape="right" class="main"}
		</p>
	</form>
{else}
	<form method="get" action="">
		<fieldset>
			<legend>Période à valoriser</legend>
			<dl>
				{input type="date" name="start" required=true label="Date de début" default=$start}
				{input type="date" name="end" required=true label="Date de fin" default=$end}
			</dl>
			<p class="submit">
				{button type="submit" label="Modifier la période" shape="right"}
			</p>
		</fieldset>
	</form>

	<form method="post" action="">
	{if !$list->count()}
		<p class="alert block">Il n'y a aucune tâche à valoriser sur cette période.<br />Vérifiez qu'il y a bien une valorisation horaire indiquée dans la configuration des tâches, ou que la période choisie correspond bien à un suivi existant.</p>
	{else}
		<p class="actions">
			{exportmenu right=true}
		</p>
		{include file="common/dynamic_list_head.tpl"}
			{foreach from=$list->iterate() item="line"}
				<tr>
					<th>{link href="all.php?id_task=%d"|args:$line.id_task label=$line.label}</th>
					<td>{$line.hours}</td>
					<td>{$line.people}</td>
					<td class="money">{$line.value|raw|money_currency:true}</td>
					<td class="money">{$line.total|raw|money_currency:true}</td>
					<td>
						{if $line.id_account}
							{link href="!acc/accounts/journal.php?id=%d"|args:$line.id_account label=$line.account_code class="num"} {$line.account_label}
						{else}
							<strong>{$line.account_code}</strong> n'est pas dans ce plan comptable
						{/if}
					</td>
				</tr>
			{/foreach}
			</tbody>
		</table>

		<p class="submit">
			{input type="hidden" name="id_year" source=$_POST}
			{csrf_field key=$csrf_key}
			{button type="submit" name="save" label="Enregistrer cette valorisation dans l'exercice '%s'"|args:$year.label shape="right" class="main"}
		</p>
	{/if}
	</form>
{/if}

</form>


{include file="_foot.tpl"}