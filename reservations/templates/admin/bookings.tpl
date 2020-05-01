{include file="admin/_head.tpl" title=$plugin.nom current="plugin_%s"|args:$plugin.id}

{if $session->canAccess('membres', Membres::DROIT_ADMIN)}
<ul class="actions">
	<li><a href="{plugin_url}">Mes réservations</a></li>
	<li class="current"><a href="{plugin_url file="bookings.php"}">Voir les inscrits</a></li>
	{if $session->canAccess('config', Membres::DROIT_ADMIN)}
		<li><a href="{plugin_url file="config.php"}">Configuration</a></li>
	{/if}
</ul>
{/if}

	<dl class="slots">
		{foreach from=$bookings item="booking"}
			{if $booking.date_change}
				<dt>{$booking.date|strftime_fr:"%A %e %B %Y"}</dt>
				<dd class="hour">
					<label>{$booking.date|strftime_fr:"%H:%M"}</label>
				</dd>
			{/if}
			<dd class="spots">
				{if $booking.id_membre}
					<a href="{$admin_url}membres/fiche.php?id={$booking.id_membre}">{$booking.nom}</a>
				{else}
					{$booking.nom}
				{/if}
			</dd>
		{/foreach}
	</dl>

{include file="admin/_foot.tpl"}
