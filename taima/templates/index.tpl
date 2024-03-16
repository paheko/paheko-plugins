{include file="_head.tpl" title="Suivi du temps"}

{include file="./_nav.tpl" current="index"}

{form_errors}

<div class="taima">
	{if $running_timers}
	<div class="block alert">
		Des chronos sont démarrés&nbsp;:
		<ul>
			{foreach from=$running_timers item="timer"}
			<li><a href="{$timer.date|taima_url}">{$timer.date|taima_date:'EEEE d MMMM yyyy'}</a></li>
			{/foreach}
		</ul>
	</div>
	{/if}
	<section class="header">
		<p class="btns">
			<a href="{$prev_url}" class="icn-btn" title="Semaine précédente">{icon shape="left"}</a>
			{button id="datepicker" shape="calendar" data-date=$day|date_format:'%Y-%m-%d'}
			<a href="{$next_url}" class="icn-btn" title="Semaine suivante">{icon shape="right"}</a>
		</p>
		<h2>{$day|taima_date:'EEEE d MMMM yyyy'}</h2>
		{if !$is_today}
		<p class="back">
			{linkbutton shape="left" href=$today_url label="Retour à aujourd'hui"}
		</p>
		{/if}
	</section>

	<ul class="weekdays">
		<li class="week"><strong>Semaine</strong><h3>{$week}</h3></li>
		{foreach from=$weekdays item="weekday"}
			<li class="day {if $weekday->day->format('Ymd') == $day->format('Ymd')}current{/if}">
				<a href="{$weekday.url}">
					<h3>
						{$weekday.day|taima_date:'EEEEE'}
						{if $weekday.timers}{$fixed_icon|raw}{/if}
					</h3>
					<strong {if !$weekday.duration}class="empty"{/if}>{$weekday.minutes_formatted}</strong>
				</a>
			</li>
		{/foreach}
		<li class="total"><span><h3>Total</h3><strong>{$week_total}</strong></span></li>
	</ul>

	<p class="actions">
		{linkbutton label="Nouvelle tâche" shape="plus" href="edit.php?date=%s"|args:$day_date target="_dialog"}
	</p>


	{if count($entries)}
		<?php $has_timers = false; ?>
		<section class="entries">
			{foreach from=$entries item="entry"}
				<article class="{if $entry.timer_started}running{/if}">
					<header>
						{if !$entry.task_label}
							<h3>—Indéfini—</h3>
						{else}
							<h3>{$entry.task_label}</h3>
						{/if}
						{if $entry.notes}
							<p class="notes">
								{$entry.notes|escape|nl2br}
							</p>
						{/if}
					</header>
					<h2 class="clock">{$entry.timer_running|taima_minutes}</h2>
					<div class="actions">
						{if $entry.timer_started}
							<?php $has_timers = true; ?>
							<a class="icn-btn stop-timer" href="{$entry.date|taima_url}&amp;stop={$entry.id}">{$animated_icon|raw} Arrêter</a>
						{elseif $is_today}
							<a class="icn-btn start-timer" href="?start={$entry.id}">{$fixed_icon|raw} Démarrer</a>
						{/if}
						{if !$entry.timer_started}
						<span>
							{linkbutton label="Modifier" title="Modifier" shape="edit" href="edit.php?date=%s&id=%d"|args:$entry.date:$entry.id target="_dialog"}
							{linkbutton label="Supprimer" title="Supprimer" shape="delete" href="delete.php?id=%d"|args:$entry.id target="_dialog"}
						</span>
						{/if}
					</div>
				</article>
			{/foreach}
		</section>

		{if $is_today && $has_timers}
			<p class="help">Si vous oubliez d'arrêter un chrono, celui-ci sera arrêté automatiquement après 13h37 sans interaction.</p>
		{/if}

	{else}

		<p class="alert block">Aucune tâche.</p>

	{/if}
</div>

<script type="text/javascript">
let icon = {$animated_icon|escape:'json'};
{literal}
function updateTimer(time) {
	var t = time.firstChild.textContent.split(':');
	t[1]++;
	if (t[1] >= 60) {
		t[1] = 0;
		t[0]++;
	}
	t[1] = ('0' + t[1]).slice(-2);
	time.firstChild.textContent = t.join(':');
	document.title = t.join(':') + ' - Chrono en cours';
}

var times = document.querySelectorAll('.running .clock');

// Mise à jour des compteurs
window.setInterval(function () {
	times.forEach(updateTimer);
}, 60*1000);

if (times.length) {
	document.head.querySelector('link[rel="icon"]').remove();
	updateTimer(times[0]);
	icon = "data:image/svg+xml;utf8," + encodeURI(icon).replace('#', '%23');
	document.head.innerHTML += `<link sizes="any" rel="icon" type="image/svg+xml" href="${icon}" />`;
}

g.script('scripts/lib/datepicker2.min.js', () => {
	var dp = $('#datepicker');
	dp.onchange = () => {
		location.search = 'day=' + dp.dataset.date;
	};
	var d = new DatePicker(dp, null, {format: 0});
});
</script>
{/literal}

{include file="_foot.tpl"}