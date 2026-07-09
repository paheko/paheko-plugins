{include file="_head.tpl" title="Clients" current="plugin_invoice"}

{include file="../_nav.tpl" current="clients"}

<nav class="tabs">
	<ul class="sub">
		<li {if !$archived}class="current"{/if}>{link href="./" label="Actuels"}</li>
		<li {if $archived}class="current"{/if}>{link href="./?archived=1" label="Archivés"}</li>
	</ul>
</nav>

{if $list->count()}
	{include file="common/dynamic_list_head.tpl"}
		{foreach from=$list->iterate() item="client"}
			<tr>
				<th>{$client.name}</th>
				<td class="actions">
					{if $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_WRITE)}
						{linkbutton shape="edit" label="Modifier" href="edit.php?id=%d"|args:$client.id}
					{/if}
				</td>
			</tr>
		{/foreach}
	</tbody>
	</table>
{else}
	<p class="alert block">Aucun client à afficher ici.</p>
{/if}

{include file="_foot.tpl"}
