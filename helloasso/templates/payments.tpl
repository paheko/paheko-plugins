{include file="_head.tpl" title="%s — %s"|args:$form.org_name,$form.name current="plugin_%s"|args:$plugin.id}

{include file="%s/templates/_menu.tpl"|args:$plugin_root current="home" current_sub="payments" show_export=true}

{include file="%s/templates/_payments_list.tpl"|args:$plugin_root details=true}

{pagination url=$list->paginationURL() page=$list.page bypage=$list.per_page total=$list->count()}

{include file="_foot.tpl"}
