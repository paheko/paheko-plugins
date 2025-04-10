{include file="_head.tpl" title="Renommer la note"}

<form method="post" action="{$self_url}" style="clear: both" data-focus="1">
	<p class="actions">
		{linkbutton shape="plus" label="Nouveau membre" href="!users/new.php?tab=1" target="_dialog"}
	</p>
	<h2 class="ruler">
		{input type="text" placeholder="Recherche rapide de membre" value=$query name="q"}
		{button type="submit" label="Chercher" shape="search"}
	</h2>
</form>

{if count($list)}
	<table class="list user_rename_list">
		<tbody>
		{foreach from=$list item="row"}
			<tr>
				<td class="num">
					{$row.number}
				</td>
				<th>
					{$row.name}
				</th>
				<td>
					{$row.email}
				</td>
				<td class="subscriptions">
				{foreach from=$row.services item="s"}
					{$s.label}
					{if $s.status == -1}
						<span class="error"><b>En retard</b> depuis le {$s.expiry_date|date_short}</span>
					{else}
						<b class="confirm">&#10003; À jour</b>
						{if $s.expiry_date}
							(expire le {$s.expiry_date|date_short})
						{/if}
					{/if}
					<br />
				{foreachelse}
					<span class="error"><b>Aucune inscription&nbsp;!</b></span>
				{/foreach}
				</td>
				<td class="actions">
					<button class="icn-btn" value="{$row.id}" data-label="{$row.name}" data-icon="&rarr;">Sélectionner</button>
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
{elseif $query !== ''}
	<div class="alert block">
		<p>Aucun résultat.</p>
		<p>
			{button shape="edit" label="Renommer la note sans la lier à un membre" id="rename_no_user"}
		</p>
	</div>
{/if}

{literal}
<script type="text/javascript">
var btn = document.querySelector('#rename_no_user');
var q = document.querySelector('#f_q');

if (btn) {
	btn.onclick = () => {
		window.parent.renameTabUser(null, q.value);
	};
}

var buttons = document.querySelectorAll('table.list button');

buttons.forEach((e) => {
	e.onclick = () => {
		window.parent.renameTabUser(e.value, e.dataset.label);
	};
});

if (buttons.length) {
	buttons[0].focus();
}

var rows = document.querySelectorAll('table tbody tr');

rows.forEach((e) => {
	e.classList.add('clickable');

	e.onclick = (evt) => {
		if (evt.target.tagName && evt.target.tagName == 'BUTTON') {
			return;
		}

		e.querySelector('button').click();
	};
});

q.focus();
var a = document.querySelector('a[href*="users/new"]');

a.addEventListener('click', () => {
	a.href = a.href.replace(/&nom=.*$|$/, '&nom=' + encodeURIComponent(q.value));
	return true;
});
</script>
{/literal}

{include file="_foot.tpl"}
