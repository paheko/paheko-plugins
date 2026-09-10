{include file="_head.tpl" title=$shortcut.label current="shortcuts_%d"|args:$shortcut.id hide_title=true}

{literal}
<style type="text/css">
html {
	min-height: 100vh;
}
body {
	height: 100%;
	overflow: hidden;
}
main#content {
	position: relative;
	height: 100%;
}
main#content iframe {
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	width: 100%;
	height: 100%;
	border: none;
}
</style>
{/literal}

<iframe src="{$shortcut.url}"></iframe>

{include file="_foot.tpl"}
