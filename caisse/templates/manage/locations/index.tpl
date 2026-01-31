{include file="_head.tpl" title="Gestion des lieux de vente"}

{include file="../_nav.tpl" current='config' subcurrent="locations"}

<div class="help block">
	<p>Si votre organisation possède plusieurs lieux physiques de vente (par exemple plusieurs boutiques), cette page permet de les gérer.</p>
	<p>Il faudra alors créer et associer des moyens de paiement à chaque lieu de vente dans l'onglet <a href="../methods/">Moyens de paiement</a>. Il est conseillé d'associer par exemple un moyen de paiement <q>Espèces</q> différent pour chaque lieu, utilisant un compte spécifique (par exemple 530 pour la caisse du siège, 531 pour la caisse de la boutique n°1, 532 pour la caisse de la boutique n°2, etc.).</p>
	<p>Lors de l'ouverture d'une caisse, le choix du lieu sera proposé.</p>
	<p>Si vous n'avez qu'un seul lieu de vente, créer un lieu de vente ici est inutile.</p>
</div>

{if !$list->count()}
	<p class="alert block">
		Aucun lieu de vente spécifique n'est configuré.
	</p>
{else}
	{include file="common/dynamic_list_head.tpl"}
			{foreach from=$list->iterate() item="row"}
				<tr>
					<th>{$row.name}</th>
					<td class="actions">
						{linkbutton href="edit.php?id=%d"|args:$row.id label="Modifier" shape="edit"}
						{linkbutton href="edit.php?id=%d&delete"|args:$row.id label="Supprimer" shape="delete" target="_dialog"}
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>

	{$list->getHTMLPagination()|raw}
{/if}

{include file="_foot.tpl"}