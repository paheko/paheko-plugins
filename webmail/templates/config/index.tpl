{include file="_head.tpl" title="Boîtes mail des membres"}

<nav class="tabs">
	<aside>
		{linkbutton shape="plus" label="Ajouter un compte" href="edit.php"}
	</aside>
</nav>

{if isset($_GET['ok'])}
	<p class="confirm block">La configuration a été enregistrée.</p>
{/if}

{if count($accounts)}
	<table class="list">
		<thead>
			<tr>
				<th scope="col">Membre</th>
				<td scope="col">Adresse e-mail</td>
				<td class="actions"></td>
			</tr>
		</thead>
		<tbody>
			{foreach from=$list item="account"}
				<th scope="row">{$account.name}</th>
				<td>{$account.address}</td>
				<td class="actions">
				</td>
			{/foreach}
		</tbody>
	</table>
{else}
	<p class="alert block">Aucun compte n'a été créé pour le moment.</p>
{/if}

<div class="block help">
	<p>
		Seuls les membres ayant une boîte mail configurée verront le menu "Mes e-mails".
	</p>
</div>


{include file="_foot.tpl"}