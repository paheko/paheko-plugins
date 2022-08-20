{include file="_head.tpl" title="Suivi du temps" plugin_css=['style.css'] current="plugin_taima"}

<?php
$timer_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22" fill="none"><circle cx="11" cy="11" r="10" stroke-width="2" /><path class="icon-timer-hand" d="M12.8 10.2L11 2l-1.8 8.2-.2.8c0 1 1 2 2 2s2-1 2-2c0-.3 0-.6-.2-.8z" /></svg>';
?>

{include file="%s/templates/_nav.tpl"|args:$plugin_root current="index"}

{form_errors}

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

<section class="taima-header">
	<p class="btns">
		<a href="{$prev_url}" class="icn-btn" title="Semaine précédente">{icon shape="left"}</a>
		<button type="button" id="datepicker" class="icn" data-date="<?=$day->format('Y-m-d');?>">{icon shape="calendar"}</button>
		<a href="{$next_url}" class="icn-btn" title="Semaine suivante">{icon shape="right"}</a>
	</p>
	<h2>{$day|taima_date:'EEEE d MMMM yyyy'}</h2>
	{if !$is_today}
		<a href={$today_url} class="icn-btn">Retour à aujourd'hui</a>
	{/if}
</section>

<ul class="taima-weekdays">
	<li class="week"><strong>Semaine</strong><h3>{$week}</h3></li>
	{foreach from=$weekdays item="weekday"}
		<li{if $weekday->day->format('Ymd') == $day->format('Ymd')} class="current"{/if}>
			<a href="{$weekday.url}">
				<h3>
					{$weekday.day|taima_date:'EEEEE'}
					{if $weekday.timers}{$timer_icon|raw}{/if}
				</h3>
				<strong{if !$weekday.duration} class="empty"{/if}>{$weekday.minutes_formatted}</strong>
			</a>
		</li>
	{/foreach}
	<li class="total"><span><h3>Total</h3><strong>{$week_total}</strong></span></li>
	<li class="add">
		{button shape="plus" data-action="add-entry" label="Nouvelle entrée"}
	</li>
</ul>

{if count($entries)}
	<table class="taima-entries">
		<tbody>
		{foreach from=$entries item="entry"}
			<tr class="<?=($entry->timer_started ? 'running' : '')?>">
				<th>
					{if !$entry.task_label}
						<h3>—Indéfini—</h3>
					{else}
						<h4>{$entry.task_label}</h4>
					{/if}
				</th>
				<td>
					<h2 class="taima-clock">{$entry.timer_running|taima_minutes}</h2>
				</td>
				<td>
					{if $entry.timer_started}
						<a class="icn-btn stop-timer" href="{$entry.date|taima_url}&amp;stop={$entry.id}">{$timer_icon|raw} Arrêter</a>
					{elseif $is_today}
						<a class="icn-btn start-timer" href="?start={$entry.id}">{$timer_icon|raw} Démarrer</a>
					{/if}
				</td>
				<td>
					{if !$entry.timer_started}
					{button data-action="edit-entry" data-entry=$entry|escape:'json' data-entry-time=$entry.timer_running|taima_minutes label="Modifier" shape="edit"}
					{/if}
				</td>
			</tr>
			{if $entry.notes}
				<tr class="notes{if $entry.timer_started} running{/if}">
					<td colspan="4">
						{$entry.notes|escape|nl2br}
					</td>
				</tr>
			{/if}
		{/foreach}
		</tbody>
	</table>

{else}

	<p class="alert block">Aucune entrée.</p>
	<p class="submit">
		{button type="button" name="add" data-action="add-entry" label="Nouvelle entrée" shape="plus" class="main"}
	</p>

{/if}

<template id="taimaDialog">
	<form method="post" action="{$self_url}" id="taimaEntryForm">
		<fieldset>
			<legend>Événement</legend>
			<dl>
				{input type="select" options=$tasks name="task_id" label="Tâche"}
				{input type="text" name="duration" placeholder="0:30" pattern="\d+[:h]\d+|\d+([.,]\d+)?" help="Formats acceptés : 1h30, 1:30, 1.5 ou 1,5. Laisser vide pour démarrer un chrono." label="Durée" size="5"}
				{input type="textarea" name="notes" label="Notes"}
			</dl>
			<p class="submit">
				{csrf_field key=$csrf_key}
				<?php $submit_label = $is_today ? 'Démarrer le chrono' : 'Enregistrer'; ?>
				{button type="submit" name="submit" label=$submit_label class="main" shape="right"}
				{button type="submit" name="delete" label="Supprimer" shape="delete"}
			</p>
			{if $is_today}
				<p class="help">Si vous oubliez d'arrêter le chrono, celui-ci sera arrêté automatiquement après 13h37 sans interaction.</p>
			{/if}
		</fieldset>
	</form>
</template>

<script type="text/javascript">
const timer_icon = '{$timer_icon|raw}';
{literal}
document.querySelectorAll('button[data-action="add-entry"]').forEach((e) => {
	e.onclick = () => {
		var c = g.openDialog(document.getElementById('taimaDialog').content);
		c.querySelector('[name="delete"]').style.display = 'none';
		var btn = c.querySelector('[type="submit"]');
		btn.name = 'add';

		let d = c.querySelector('#f_duration');

		d.onkeyup = function (e) {
			btn.innerText = (e.target.value == '') ? 'Démarrer le chrono' : 'Enregistrer';
		};

		d.focus();

		return false;
	};
});

document.querySelectorAll('button[data-action="edit-entry"]').forEach((e) => {
	e.onclick = () => {
		var c = g.openDialog(document.getElementById('taimaDialog').content);
		var data = JSON.parse(e.dataset.entry);

		var d = c.querySelector('#f_duration');
		var btn = c.querySelector('[type="submit"]');
		btn.name = 'edit[' + data.id + ']';
		btn.innerText = 'Enregistrer';

		d.value = e.dataset.entryTime;
		d.focus();
		d.select();

		c.querySelector('#f_notes').value = data.notes;
		c.querySelector('#f_task_id').value = data.task_id;

		c.querySelector('[name="delete"]').name = 'delete[' + data.id + ']';

		return false;
	};
});

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

var times = document.querySelectorAll('.running .taima-clock');

// Mise à jour des compteurs
window.setInterval(function () {
	times.forEach(updateTimer);
}, 60*1000);

document.head.querySelector('link[rel="icon"]').remove();
let icon = timer_icon;

if (times.length) {
	icon = icon.replace(/(<svg.*?>)/, '$1<style>svg { animation: spinner 3s linear infinite; } path { stroke: rgb(0, 180, 180); stroke-width: 2px; fill: rgb(0, 180, 180); } circle { stroke: rgb(0, 180, 180); } @keyframes spinner { to {transform: rotate(360deg);} }</style>');

	updateTimer(times[0]);
}
else {
	icon = icon.replace(/(<svg.*?>)/, '$1<style>path { stroke: gray; stroke-width: 2px; fill: gray; } circle { stroke: gray; }</style>');
}

icon = "data:image/svg+xml;utf8," + encodeURI(icon).replace('#', '%23');
document.head.innerHTML += `<link sizes="any" rel="icon" type="image/svg+xml" href="${icon}" />`;

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