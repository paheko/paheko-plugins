{include file="_head.tpl" title="Contacts" current="plugin_pim" hide_title=true plugin_css=['contacts/contacts.css']}

{include file="./_nav.tpl"}

{if $list->count()}
	{include file="common/dynamic_list_head.tpl"}
	{foreach from=$list->iterate() item="row"}
		<tr>
			<td class="avatar{if $row.has_photo} photo{/if}"><a href="details.php?id={$row.id}" target="_dialog"><img src="{$row.photo}" alt="Photo" /></a></td>
			<td>{$row.first_name}</td>
			<td>{$row.last_name}</td>
			<td>{$row.title}</td>
			<td class="actions">
				{linkbutton href="details.php?id=%d"|args:$row.id label="Contact" shape="user" target="_dialog"}
				{linkbutton href="edit.php?id=%d"|args:$row.id label="Modifier" shape="edit" target="_dialog"}
				{linkbutton href="delete.php?id=%d"|args:$row.id label="Supprimer" shape="delete" target="_dialog"}
			</td>
		</tr>
	{/foreach}
	</tbody>
	</table>
{else}
	<p class="block alert">Aucun contact à afficher ici.</p>
{/if}

{include file="_foot.tpl"}
