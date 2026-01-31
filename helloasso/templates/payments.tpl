{include file="_head.tpl" title="%s — %s"|args:$form.org_name,$form.name}

{include file="./_menu.tpl" current="home" current_sub="payments" show_export=true}

{include file="./_payments_list.tpl" details=true}

{$list->getHTMLPagination()|raw}

{include file="_foot.tpl"}
