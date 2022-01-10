{include file="admin/_head.tpl" title="%s — %s"|args:$form.org_name,$form.name current="plugin_%s"|args:$plugin.id}

{include file="%s/templates/_menu.tpl"|args:$plugin_root current="home" current_sub="items" show_export=true}

{include file="%s/templates/_items_list.tpl"|args:$plugin_root details=true}

{pagination url=$list->paginationURL() page=$list.page bypage=$list.per_page total=$list->count()}

{include file="admin/_foot.tpl"}
