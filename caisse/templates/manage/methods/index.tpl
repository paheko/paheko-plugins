{include file="admin/_head.tpl" title="Gestion moyens de paiement" current="plugin_%s"|args:$plugin.id}

{include file="%s/manage/_nav.tpl"|args:$pos_templates_root current='methods'}

<table class="list">
	<thead>
		<tr>
			<th>Nom</th>
			<td>Compte</td>
			<td>Minimum</td>
			<td>Maximum</td>
			<td>Activé</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
		{foreach from=$list item="method"}
			<tr>
				<th>{$method.name}</th>
				<td>{$method.account}</td>
				<td>{$method.min|raw|money_currency}</td>
				<td>{$method.max|raw|money_currency}</td>
				<td>{if $method.enabled}Activé{else}<strong class="error">Désactivé</strong>{/if}</td>
				<td class="actions">
					{linkbutton href="products.php?id=%d"|args:$method.id label="Produits" shape="menu"}
					{linkbutton href="edit.php?id=%d"|args:$method.id label="Modifier" shape="edit" target="_dialog"}
					{linkbutton href="edit.php?id=%d&delete"|args:$method.id label="Supprimer" shape="delete" target="_dialog"}
				</td>
			</tr>
		{/foreach}
	</tbody>
</table>

{include file="admin/_foot.tpl"}