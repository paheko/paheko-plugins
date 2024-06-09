{include file="_head.tpl" title="Participant⋅e⋅s à la discussion" current="plugin_%s"|args:$plugin.id}

{if $session->canAccess($session::SECTION_USERS, $session::ACCESS_ADMIN)}
	<nav class="tabs">
		<aside>
			{linkbutton shape="plus" label="Inviter" href="!p/chat/invite.php?id=%d"|args:$channel.id}
		</aside>
	</nav>
{/if}

<section class="chat-users">
	<ul>
		{foreach from=$users item="user"}
			<li>{chat_avatar object=$user name=true direct=true online=true}</li>
		{/foreach}
	</ul>
</section>

{include file="_foot.tpl"}
