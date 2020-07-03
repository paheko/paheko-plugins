{foreach from=$list item="m"}
	<li onclick="selectUserRename({$m.id}, &quot;{$m.identite}&quot;);">
		<h5>{$m.numero}</h5>
		<h3>{$m.identite}</h3>
		<h4>{$m.email}</h4>
		{foreach from=$m.subscriptions item="cotisation"}
		<p>{$cotisation.intitule} —
		{if !$cotisation.a_jour}
			<span class="error"><b>En retard</b> depuis le {$cotisation.expiration|format_sqlite_date_to_french}</span>
		{else}
			<b class="confirm">&#10003; À jour</b>
			{if $cotisation.expiration}
				(expire le {$cotisation.expiration|format_sqlite_date_to_french})
			{/if}
		{/if}
		</p>
		{/foreach}
	</li>
{/foreach}