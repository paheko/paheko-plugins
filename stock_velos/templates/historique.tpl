{include file="_head.tpl" title="%s vélos sortis du stock"|args:$total}

{include file="./_nav.tpl" current="historique"}

{include file="common/dynamic_list_head.tpl"}

    {foreach from=$list->iterate() item="row"}
        <tr>
            <th class="num"><a href="{plugin_url query=1}id={$row.id|escape}">{$row.id|escape}</a></th>
            <td>{$row.type|escape}</td>
            <td>{$row.roues|escape}</td>
            <td>{$row.genre|escape}</td>
            <td>{$row.modele|escape}</td>
            <td>{$row.couleur|escape}</td>
            <td>{if empty($row.prix)}--{elseif $row.prix < 0}à&nbsp;démonter{else}{$row.prix|escape} €{/if}</td>
            <td>{$row.date_sortie|date_short}</td>
            <td>{$row.raison_sortie|escape}</td>
        </tr>
    {/foreach}
    </tbody>
</table>

{$list->getHTMLPagination()|raw}

{include file="_foot.tpl"}