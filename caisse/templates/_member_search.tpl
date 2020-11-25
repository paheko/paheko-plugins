{foreach from=$list item="m"}
	<li onclick="selectUserRename({$m.id}, &quot;{$m.identite}&quot;);">
		<h5>{$m.numero}</h5>
		<h3>{$m.identite}</h3>
		<h4>{$m.email}</h4>
		<p>
			Cotisation
			{if $m.status == -1}
				<span class="error"><b>En retard</b> depuis le {$m.expiry_date|date_short}</span>
			{else}
				<b class="confirm">&#10003; À jour</b>
				{if $m.expiry_date}
					(expire le {$m.expiry_date|date_short})
				{/if}
			{/if}
		</p>
	</li>
{/foreach}