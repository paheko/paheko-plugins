<form method="post" action="{$self_url}">

{if $booking}
<fieldset class="mine">
	<legend>Ma réservation</legend>
	<dl>
		<dt>Vous avez réservé le créneau suivant&nbsp;:</dt>
		<dd class="date">{$booking.date|strftime_fr:"%A %e %B %Y à %H:%M"}</dd>
		{if $booking.numero}<dd>Numéro de membre : {$booking.numero}</dd>{/if}
		{if $booking.nom}<dd>Prénom : {$booking.nom}</dd>{/if}
		<dd><input type="submit" name="cancel" value="Annuler ma réservation" /></dd>
	</dl>
</fieldset>

{/if}

<fieldset>
	<legend>{if !empty($title)}{$title}{else}Créneaux disponibles{/if}</legend>
	{if !count($slots)}
		<p class="alert">Aucun créneau disponible.
	{else}
		<dl class="slots">
			{foreach from=$slots item="slot"}
				{if $slot.date_change}
					<dt>{$slot.timestamp|strftime_fr:"%A %e %B %Y"}</dt>
				{/if}
				<dd class="hour available_{$slot.available}">
					{if $slot.available && !$booking}
						<label><input type="radio" class="n-radio" name="slot" value="{$slot.id}={$slot.date}" /> {$slot.heure}</label>
					{else}
						{$slot.heure}
					{/if}
				</dd>
				<dd class="spots available_{$slot.available}">
					<em>{$slot.available} places disponibles</em>
				</dd>
			{/foreach}
		</dl>

		{if !$booking && !empty($ask_name)}
		<dl class="info">
			<dt><label for="f_numero">Numéro de membre&nbsp;:</label></dt>
			<dd><input type="number" name="numero" id="f_numero" placeholder="Numéro" /></dd>
			<dt class="help">Ou, si vous n'êtes pas encore adhérent ou ne vous souvenez pas de votre numéro&nbsp;:</dt>
			<dt><label for="f_nom">Prénom&nbsp;: </label></dt>
			<dd><input type="text" name="nom" id="f_nom" placeholder="Prénom" /></dd>
		</dl>
		{/if}

		{if !$booking}
		<p class="submit">
			<input type="submit" name="book" value="Confirmer la réservation" />
		</p>
		{/if}
	{/if}
</fieldset>

</form>
