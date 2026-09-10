{include file="_head.tpl" title="Configurer les raccourcis"}

<nav class="tabs">
	<aside>
		{linkbutton shape="plus" href="edit.php" label="Nouveau raccourci"}
	</aside>
</nav>

{if !count($list)}
	<p class="alert block">Aucun raccourci n'a été créé.<br />
		{linkbutton shape="plus" href="edit.php" label="Nouveau raccourci"}
	</p>
{else}
	<form method="post" action="{$self_url_no_qs}">
		<table class="list">
			<thead>
				<tr>
					<td></td>
					<td>Icône</td>
					<th>Libellé</th>
					<td>Cadre intégré</td>
					<td>Page d'accueil</td>
					<td>Menu</td>
					<td class="actions"></td>
				</tr>
			</thead>
			<tbody>
				{foreach from=$list item="row"}
				<tr>
					<td>
						<span class="draggable" title="Cliquer, glisser et déposer pour modifier l'ordre">{button shape="menu"}</span>
						<input type="hidden" name="sort_order[]" value="{$row.id}" />
					</td>
					<td>{if $row.icon}<img src="{$row->getIconURL()}" alt="" />{else}{icon shape=$row.shape}{/if}</td>
					<th>{$row.label}</th>
					<td>{if $row.iframe}{tag label="Cadre intégré" status="green"}{/if}</td>
					<td>{if $row.home}{tag label="Page d'accueil" status="tan"}{/if}</td>
					<td>{if $row.menu}{tag label="Menu" status="greyblue"}{/if}</td>
					<td class="actions">
						{button shape="up" title="Déplacer vers le haut" class="up"}
						{button shape="down" title="Déplacer vers le bas" class="down"}
						{linkbutton shape="delete" href="delete.php?id=%d"|args:$row.id label="Supprimer"}
						{linkbutton shape="edit" href="edit.php?id=%d"|args:$row.id label="Modifier"}
					</td>
				</tr>
				{/foreach}
			</tbody>
		</table>

		<p class="help">
			Cliquer et glisser-déposer sur une ligne pour en changer l'ordre.
		</p>

		<p class="submit">
			{csrf_field key=$csrf_key}
			{button type="submit" name="save" label="Enregistrer l'ordre" shape="right"}
		</p>
	</form>
	<script type="text/javascript" src="{$admin_url}static/scripts/dragdrop-table.js"></script>
	{literal}
	<style type="text/css">
	table td img { max-height: 32px; vertical-align: middle; }
	</style>
	{/literal}
{/if}

{include file="_foot.tpl"}
