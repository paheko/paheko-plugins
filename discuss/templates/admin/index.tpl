{include file="_head.tpl" title="Manage members"}

{if $error}
	<p class="error">{$error}</p>
{/if}

<form method="post" action="">
<fieldset>
	<legend>{{Subscribe members using their e-mail addresses}}</legend>
	<dl>
		{input type="textarea" name="add" label="Subscribe the following e-mail addresses to this list" help="(one address per line)"}
		{input type="checkbox" name="send_msg" value="1" label="Send the welcome message"}
	</dl>
	<p class="submit"><input type="submit" value="{{Add}} &rarr;" /></p>
</fieldset>
</form>

{if !$members_count}
	<p class="alert block">{{There's nobody subscribed to this list.}}</p>
{else}
<form method="post" action="">
	<table class="list">
		<thead>
			<tr>
				<td></td>
				<th>{{Address}}</th>
				<td>{{Subscribed since}}</td>
				<td>{{Number of posts}}</td>
				<td>Number of bounces</td>
				<td>Moderator?</td>
				<td>Receive messages?</td>
			</tr>
		</thead>
		<tbody>

	{foreach from=$members item="user"}
			<tr>
				<td><input type="checkbox" name="checked[]" value="{$user.id}" /></td>
				<th>{$user.email}</th>
				<td>{$user.stats_since}</td>
				<td>{$user.stats_posts}</td>
				<td>
					{$user.stats_bounced}
					{if $user.stats_bounced}
						<small><a href="?reset={$user.id}">[Reset]</a></small>
				</td>
				<td>{if $user->isModerator()}<b>Moderator</b>{/if}</td>
				<td>{if $user->isSubscribedToEmails()}Yes{else}No{/if}</td>
			</tr>
	{/foreach}

		</tbody>
	</table>

	<fieldset>
		<legend>Action on selected members</legend>
		<dl>
			<dt>
				<input type="submit" name="remove" value="Remove from list" />
				<label><input type="checkbox" name="send_msg" value="1" /> Send the goodbye message</label></dd>
			</dt>
			<dt>
				<input type="submit" name="set_moderator" value="Set moderator status" />
				<input type="submit" name="unset_moderator" value="Remove moderator status" />
			</dt>
		</dl>
	</fieldset>
</form>
{/if}

{include file="_foot.tpl"}
