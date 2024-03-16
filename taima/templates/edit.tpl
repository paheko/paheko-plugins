{if $entry->exists()}
	{assign var="title" value="Modifier une tâche"}
{else}
	{assign var="title" value="Ajouter une tâche"}
{/if}
{include file="_head.tpl" title=$title}

{if isset($_GET.ok)}
	<p class="confirm block">Tâche enregistrée.</p>
{/if}

{form_errors}

<form method="post" action="">
	<fieldset>
		<legend>{$title}</legend>
		<dl>
			{if !$date}
				{input type="date" label="Date" required=true name="date" default=$now source=$entry}
				{input type="list" name="user" required=false label="Membre" help="Laisser vide pour une tâche bénévole qui n'est pas liée à un membre en particulier" multiple=false target="!users/selector.php" default=$selected_user}
			{/if}
			{input type="select" options=$tasks name="task_id" label="Catégorie" source=$entry default_empty="— Non spécifiée —"}
			{if $date && $is_today}
				{input type="text" name="duration" placeholder="0:30" pattern="\d+[:h]\d+|\d+([.,]\d+)?" help="Formats acceptés : 1h30, 1:30, 1.5 ou 1,5. Laisser vide pour démarrer un chrono." label="Durée" size="5" required=false default=$entry_duration}
			{else}
				{input type="text" name="duration" placeholder="0:30" pattern="\d+[:h]\d+|\d+([.,]\d+)?" help="Formats acceptés : 1h30, 1:30, 1.5 ou 1,5." label="Durée" size="5" required=true default=$entry_duration}
			{/if}
			{input type="textarea" name="notes" label="Notes" source=$entry}
		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="save" label=$submit_label class="main" shape="right"}
	</p>
</form>

<script type="text/javascript">
let d = $('#f_duration');
let focusDuration = () => {ldelim} d.focus(); d.select(); {rdelim};
let s = $('#f_task_id');
s.onchange = focusDuration;

{if $entry->exists()}
	focusDuration();
{else}
	s.focus();
{/if}

{if $is_today}
	d.onkeyup = () => $('button.main')[0].innerText = (d.value == '') ? 'Démarrer le chrono' : 'Enregistrer';
{/if}
</script>

{include file="_foot.tpl"}