{include file="_head.tpl" title="Contacts" current="plugin_pim" hide_title=true plugin_css=['contacts/contacts.css']}

{include file="./_nav.tpl"}

{if $list->count()}
	<section class="contacts">
	{foreach from=$list->iterate() item="row"}
		<article>
			<a href="details.php?id={$row.id}" target="_dialog">
				<figure class="avatar{if $row.has_photo} photo{/if}"><img src="{$row.photo}" alt="Photo" /></figure>
				<h2>{$row.first_name} {$row.last_name}</h2>
				<h4>{$row.title}</h4>
			</a>
			<p class="actions">
				{linkbutton href="edit.php?id=%d"|args:$row.id label="Modifier" shape="edit" target="_dialog"}
				{linkbutton href="delete.php?id=%d"|args:$row.id label="Supprimer" shape="delete" target="_dialog"}
			</p>
		</article>
	{/foreach}
	</section>
{else}
	<p class="block alert">Aucun contact à afficher ici.</p>
{/if}

{include file="_foot.tpl"}
