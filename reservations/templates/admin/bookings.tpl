{include file="admin/_head.tpl" title=$plugin.nom current="plugin_%s"|args:$plugin.id}

{include file="%s/templates/admin/_menu.tpl"|args:$plugin_root current="bookings"}

<dl class="slots">
	{foreach from=$bookings item="booking"}
		{if $booking.date_change}
			<dt>
				{$booking.date|strftime_fr:"%A %e %B %Y"}
			</dt>
		{/if}
		{if $booking.hour_change}
		<dd class="hour">
			<b>{$booking.date|strftime_fr:"%H:%M"}</b>
		</dd>
		{/if}
		<dd class="spots">
			<span class="actions">
				<a href="{$self_url}?delete={$booking.id}" title="Supprimer" class="icn" data-action="delete">✘</a>
			</span>
			{if $booking.id_membre}
				<a href="{$admin_url}membres/fiche.php?id={$booking.id_membre}">{$booking.nom}</a>
			{else}
				{$booking.nom}
			{/if}
		</dd>
	{/foreach}
</dl>

<script type="text/javascript">
{literal}
document.querySelectorAll('a[data-action="delete"]').forEach(function (e) {
	e.onclick = function () { return confirm("Supprimer ?"); };
});
{/literal}
</script>

{include file="%s/templates/_form.tpl"|args:$plugin_root ask_name=true booking=null title="Réserver pour un adhérent"}

{include file="admin/_foot.tpl"}
