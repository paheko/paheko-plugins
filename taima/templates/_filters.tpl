<form method="get" action="" class="{if !$filters.start}hidden {/if}noprint" id="filterForm">
{foreach from=$filters key="key" item="value"}
	{if $key === 'start' || $key === 'end'}<?php continue; ?>{/if}
	<input type="hidden" name="{$key}" value="{$value}" />
{/foreach}
	<fieldset>
		<legend>Filtrer par date</legend>
		<p>
			<label for="f_after">Du</label>
			{input type="date" name="start" source=$filters default=$default_start}
			<label for="f_before">au</label>
			{input type="date" name="end" source=$filters default=$default_end}
			{button type="submit" label="Filtrer" shape="right"}
			<input type="submit" value="Annuler" onclick="this.form.querySelectorAll('input:not([type=hidden]), select').forEach((a) => a.disabled = true); this.form.submit();" />
		</p>
	</fieldset>
</form>