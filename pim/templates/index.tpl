{include file="_head.tpl" title="Agenda" current="plugin_pim" hide_title=true plugin_css=['calendar.css']}

<nav class="tabs">
	<aside>
	</aside>

	<div class="months">
		{linkbutton label=$prev_year shape="left" href="?y=%d&m=%d"|args:$prev_year:$month}
		{linkbutton label=$prev|strftime:'%B' shape="left" href=$prev|strftime:'?y=%Y&m=%m'}
		{linkbutton label=$date|strftime:"%B %Y" href="./" class="current" title="Retourner à aujourd'hui"}
		{linkbutton label=$next|strftime:'%B' shape="right" href=$next|strftime:'?y=%Y&m=%m' class="right"}
		{linkbutton label=$next_year shape="right" href="?y=%d&m=%d"|args:$next_year:$month class="right"}
		{linkbutton shape="menu" label="Catégories" href="categories/"}
		{linkbutton shape="users" label="Contacts" href="contacts/"}
		{linkbutton shape="plus" label="Nouvel événement" href="edit.php"}
	</div>
</nav>

<table class="calendar weeks-{$calendar|count}">
	<thead>
		<tr>
			<th>Lundi</th>
			<th>Mardi</th>
			<th>Mercredi</th>
			<th>Jeudi</th>
			<th>Vendredi</th>
			<th>Samedi</th>
			<th>Dimanche</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$calendar item="week"}
			<tr>
				{foreach from=$week item="day"}
				<td class="{$day.class}" data-date="{$day.date|strftime:'%Y-%m-%d'}">
					<h3 title="{$day.saint}">{$day.date|strftime:'%d'}</h3>
					<ul>
						{if $day.holiday}
							<li class="holiday">{$day.holiday}</li>
						{/if}
						{foreach from=$day.events item="event"}
						<li class="{$event.class}" style="{$event.style}">
							{if $event.starts}<b>{$event.starts}</b>{/if}
							<a href="{$event.url}">{$event.title}</a>
							{if $event.ends}<b>⇢ {$event.ends}</b>{/if}
							{if $event.subtitle}<em>{$event.subtitle}</em>{/if}
						</li>
						{/foreach}
					</ul>
				</td>
				{/foreach}
			</tr>
		{/foreach}
	</tbody>
</table>

<script type="text/javascript" src="../static/agenda.js?2019"></script>

{include file="_foot.tpl"}
