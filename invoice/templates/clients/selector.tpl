{include file="_head.tpl" title="Rechercher un client"}

<form method="post" action="{$self_url}">
	<h2 class="ruler">
		<input type="text" placeholder="Recherche rapide de client" value="{$search}" name="search" />
		<input type="submit" value="Chercher &rarr;" />
	</h2>
</form>

{if $list}
	<table class="list">
		<tbody>
		{foreach from=$list->iterate() item="row"}
			<tr>
				<th>
					{$row.name}
				</th>
				<td class="actions">
					<button class="icn-btn" value="{$row.id}" data-label="{$row.name}" data-icon="&rarr;">Sélectionner</button>
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>

	{$list->getHTMLPagination()|raw}
{/if}

{literal}
<script type="text/javascript">
var buttons = document.querySelectorAll('button');

buttons.forEach((e) => {
	e.onclick = () => {
		window.parent.g.inputListSelected(e.value, e.getAttribute('data-label'));
	};
});

if (buttons.length) {
	buttons[0].focus();
}

var rows = document.querySelectorAll('table tbody tr');

if (rows.length == 1) {
	rows[0].querySelector('button').click();
}

rows.forEach((e) => {
	e.classList.add('clickable');

	e.onclick = (evt) => {
		if (evt.target.tagName && evt.target.tagName == 'BUTTON') {
			return;
		}

		e.querySelector('button').click();
	};
});

document.querySelector('input').focus();
</script>
{/literal}

{include file="_foot.tpl"}