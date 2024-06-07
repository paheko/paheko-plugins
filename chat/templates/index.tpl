{include file="_head.tpl" title="Chat" current="plugin_%s"|args:$plugin.id}

<section class="channels">
	<table>
		{foreach from=$channels item="channel"}
		<tr>
			<th>{link href="?id=%d"|args:$channel.id label=$channel.name}</th>
		</tr>
		{/foreach}
	</table>
</section>


{include file="_foot.tpl"}
