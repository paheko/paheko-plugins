{include file="_head.tpl" title=$list.title}

{if $_GET.msg == 'JOIN_OK'}
	<p class="confirm">You will receive an email to confirm your subscription request.</p>
{elseif $_GET.msg == 'LEAVE_OK'}
	<p class="confirm">You have been unsubscribed, bybye!</p>
{elseif $_GET.msg == 'ARCHIVES_FAIL'}
	<p class="error">You don't have access to this list archives.</p>
{/if}

{if $error}
	<p class="error">{$error}</p>
{/if}

{if !$logged_user}
<form method="post" action="?form">
	<fieldset>
		<legend>Manage my subscription</legend>
		<dl>
			{input name="email" label="E-Mail address" required=true type="email"}
		</dl>
		<p>
		{if $list.open}
			<input type="submit" name="join" value="Subscribe to this list" />
		{/if}
			<input type="submit" name="leave" value="Unsubscribe" />
		</p>
	</fieldset>
</form>
{/if}

{if $show_threads}
	{include file="_search_form.tpl"}
	{include file="_threads.tpl"}
{elseif !$logged_user}
	{include file="_login.tpl" label="Login to read the messages"}
{else}
	<p class="alert">{{You don't have access to the messages.}}</p>
{/if}

{include file="_foot.tpl"}