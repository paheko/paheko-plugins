{include file="_head.tpl" title="Recharger les données depuis HelloAsso"}

{include file="%s/templates/_menu.tpl"|args:$plugin_root current="sync"}

{if $_GET.msg === 'CONNECTED'}
<p class="confirm block">
	Connexion à l'API HelloAsso effectuée !
</p>
{/if}


{if $last_sync}
	<p class="help">
		Le dernier rechargement date du {$last_sync|date}.
	</p>
{else}
	<p class="alert block">Cliquer sur le bouton ci-dessous pour récupérer les données depuis HelloAsso.</p>
{/if}

{if !$last_sync && $last_sync > (new \DateTime('1 hour ago'))}
	<p class="alert block">Il n'est pas possible d'effectuer plus d'un rechargement manuel par heure.</p>
{else}
	<form method="post" action="{$self_url}">
		{if $last_sync}
		<fieldset>
			<legend>Données à recharger</legend>
			<dl>
				{input type="checkbox" name="forms" value=1 label="Campagnes" help="nécessaire si vous avez ajouté ou modifié une campagne" source=$sync}
				{input type="checkbox" name="orders" value=1 label="Commandes" source=$sync}
				{*input type="checkbox" name="payments" value=1 label="Remboursements, paiements en 3 fois, et paiements mensuels" default=1*}
			</dl>
		</fieldset>
		{/if}
		<p class="submit">
			{csrf_field key=$csrf_key}
			{button type="submit" name="sync" value=1 label="Recharger les données" shape="right" class="main"}
		</p>
	</form>
{/if}

{include file="_foot.tpl"}
