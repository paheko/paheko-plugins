{include file="_head.tpl" title="Gestion moyens de paiement"}

{include file="../_nav.tpl" current='methods'}

<table class="list">
	<thead>
		<tr>
			<th>Nom</th>
			<td>Type</td>
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
				<td>{$method::TYPES_LABELS[$method.type]}</td>
				<td>{$method.account}</td>
				<td>{$method.min|raw|money_currency}</td>
				<td>{$method.max|raw|money_currency}</td>
				<td>{if $method.enabled}{tag label="Activé" color="darkgreen"}{else}{tag label="Désactivé" color="#999"}{/if}</td>
				<td class="actions">
					{linkbutton href="products.php?id=%d"|args:$method.id label="Produits" shape="menu"}
					{linkbutton href="edit.php?id=%d"|args:$method.id label="Modifier" shape="edit"}
					{linkbutton href="edit.php?id=%d&delete"|args:$method.id label="Supprimer" shape="delete" target="_dialog"}
				</td>
			</tr>
		{/foreach}
	</tbody>
</table>

{include file="_foot.tpl"}