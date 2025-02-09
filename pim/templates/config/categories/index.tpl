{include file="_head.tpl" title="Catégories de l'agenda" current="plugin_pim" hide_title=true plugin_css=['calendar.css']}

<nav class="tabs">
	<aside>
		{linkbutton href="edit.php" label="Nouvelle catégorie" shape="download"}
		{linkbutton href="?export=all" label="Exporter tout" shape="plus" target="_dialog"}
	</aside>
	<ul>
		<li><a href="../../">Agenda</a></li>
		<li><a href="../../contacts/">Contacts</a></li>
		<li class="current"><a href="./">Configuration</a></li>
	</ul>
	<ul class="sub">
		<li class="current"><a href="./">Catégories de l'agenda</a></li>
	</ul>
</nav>

<table class="list categories">

	{foreach from=$list item="cat"}
		<tr>
			<td class="color"><span class="cat_color" style="--hue: {$cat.color}"></span></td>
			<th style="--hue: {$cat.color}">{$cat.title}</th>
			<td>{if $cat.is_default}(par défaut){/if}</td>
			<td class="actions">
				{if !$cat.is_default}{linkbutton href="?set_default=%d"|args:$cat.id label="Par défaut" shape="check"}{/if}
				{linkbutton href="?export=%d"|args:$cat.id label="Exporter" shape="download"}
				{linkbutton href="edit.php?id=%d"|args:$cat.id label="Modifier" shape="edit" target="_dialog"}
				{linkbutton href="delete.php?id=%d"|args:$cat.id label="Supprimer" shape="delete" target="_dialog"}
			</td>
		</tr>
	{/foreach}

</table>

{include file="_foot.tpl"}
