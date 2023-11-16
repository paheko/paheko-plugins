{foreach from=$list item="u"}
	<button type="button" data-name="{$u.name}" data-id="{$u.id}">
		<h5>{$u.number}</h5>
		<h3>{$u.name}</h3>
		<h4>{$u.email}</h4>
		{foreach from=$u.services item="s"}
			<p>
				{$s.label}
				{if $s.status == -1}
					<span class="error"><b>En retard</b> depuis le {$s.expiry_date|date_short}</span>
				{else}
					<b class="confirm">&#10003; À jour</b>
					{if $s.expiry_date}
						(expire le {$s.expiry_date|date_short})
					{/if}
				{/if}
			</p>
		{foreachelse}
			<p><span class="error"><b>Aucune inscription&nbsp;!</b></span></p>
		{/foreach}
	</button>
{/foreach}
{if empty($u)}
	<p class="alert block">Aucun résultat.</p>
{/if}