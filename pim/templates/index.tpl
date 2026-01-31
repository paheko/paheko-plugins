{include file="_head.tpl"
	title="Agenda"
	current="plugin_pim"
	hide_title=true
	plugin_css=['calendar.css']
	upload_here_url=$upload_url
	layout="calendar"}

<nav class="tabs">
	<aside class="months">
		{linkbutton label=$prev_year shape="left" href="?y=%d&m=%d"|args:$prev_year:$month class="year"}
		{linkbutton label=$prev|strftime:'%B' shape="left" href=$prev|strftime:'?y=%Y&m=%m' class="month"}
		{linkbutton label=$date|strftime:"%B %Y" href="./" class="current" title="Retourner à aujourd'hui"}
		{linkbutton label=$next|strftime:'%B' shape="right" href=$next|strftime:'?y=%Y&m=%m' class="right month"}
		{linkbutton label=$next_year shape="right" href="?y=%d&m=%d"|args:$next_year:$month class="right year"}
		{linkbutton shape="plus" label="Nouvel événement" href="edit.php" target="_dialog"}
	</aside>
	<ul>
		<li class="current"><a href="./">Agenda</a></li>
		<li><a href="contacts/">Contacts</a></li>
		<li><a href="config/">Configuration</a></li>
	</ul>
</nav>

{if $is_new}
<div class="alert block">
	<h3>Bienvenue dans votre agenda !</h3>
	<p>Cet agenda n'est visible <strong>que par vous-même</strong>, personne d'autre dans l'association n'y a accès.</p>
	<p>Il n'est pas encore possible de créer un agenda partagé.</p>
</div>
{/if}

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
					<h3>
						{$day.date|strftime:'%d'}
						{if $day.observance}
							<small data-emoji="{$day.observance.emoji}">
								<span>
								{if $day.observance.url}
									{link href=$day.observance.url label=$day.observance.label}
								{else}
									{$day.observance.label}
								{/if}
								</span>
							</small>
						{/if}
					</h3>
					<ul>
						{if $day.holiday}
							<li class="holiday">{$day.holiday}</li>
						{/if}
						{foreach from=$day.events item="event"}
						<li class="{$event.class}" style="{$event.style}">
							{if $event.starts}<b>{$event.starts}</b>{/if}
							{link href=$event.url target=$event.target label=$event.title}
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

<script type="text/javascript" src="calendar.js"></script>

{include file="_foot.tpl"}
