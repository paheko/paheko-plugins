{include file="_head.tpl" title=$plugin.nom current="plugin_%s"|args:$plugin.id}

{include file="%s/templates/admin/_menu.tpl"|args:$plugin_root current="index"}

{include file="%s/templates/_form.tpl"|args:$plugin_root ask_name=false}

<article class="wikiContent">
	{$config.text|raw|format_skriv}
</article>

{include file="_foot.tpl"}
