{include file="_head.tpl" title="%s — %s"|args:$f.org_name:$f.name}

{include file="./_menu.tpl" current="home" current_sub="payments" show_export=true}

{if !$list->count()}
	<p class="alert block">Il n'y a aucun paiement pour cette campagne.</p>
{else}
	{include file="./_payments_list.tpl" details=true}

	{$list->getHTMLPagination()|raw}
{/if}

{include file="_foot.tpl"}
