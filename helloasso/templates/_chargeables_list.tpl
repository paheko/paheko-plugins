
{include file="common/dynamic_list_head.tpl"}

{assign var='chargeable_need_config' value=false}

	{foreach from=$list->iterate() item="row"}

		{if 
			($plugin.config.accounting && $row.type !== Plugin\HelloAsso\Entities\Chargeable::FREE_TYPE && (!$row.id_credit_account || !$row.id_debit_account))
			|| ($row.register_user === null)
		}
			{assign var='need_configuration' value=true}
		{else}
			{assign var='need_configuration' value=false}
		{/if}
		<tr {if $need_configuration}class="awaits_account_configuration"{/if}>
			<td class="num"><a href="{"chargeable.php?id=%s"|args:$row.id}">{$row.id}</a></td>
			<td>{$row.type_label}</td>
			<td>{$row.label}</td>
			<td class="money">{$row.amount|money_currency|raw}</td>
			<td>{$row.register_user}</td>
			<td class="num"><a href="{$admin_url}acc/accounts/journal.php?id={$row.id_credit_account|intval}">{$row.credit_account}</a></td>
			<td class="num"><a href="{$admin_url}acc/accounts/journal.php?id={$row.id_debit_account|intval}">{$row.debit_account}</a></td>

			<td class="actions">
				{if $need_configuration}
					{assign var='button_label' value='Configurer'}
					{assign var='button_shape' value='settings'}
					{assign var='chargeable_need_config' value=true}
				{else}
					{assign var='button_label' value='Modifier'}
					{assign var='button_shape' value='edit'}
				{/if}
				{if $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_WRITE)}
					{linkbutton href="chargeable.php?id=%s"|args:$row.id shape=$button_shape label=$button_label}
				{/if}
			</td>
		</tr>

	{/foreach}

	</tbody>
</table>

<p class="help block">Les articles (tarifs et options) configurés dans l'administration de HelloAsso ne peuvent apparaître ici qu'uniquement <em>après</em> avoir été commandés au moins une fois.</p>

{if $chargeable_need_config}
	<p class="alert block">
		Les articles en rouge nécessitent une configuration
		{if $session->canAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN)} de votre part.
			<br /><br />Vous pouvez soit les configurer un-à-un depuis leur bouton de configuration soit tous d'un coup depuis <a href="{$plugin_admin_url}sync.php">la page de synchronisation</a>.
		{else}
			. Merci de contacter votre administrateur/trice.
		{/if}
	</p>
{/if}