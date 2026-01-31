{include file="_head.tpl"}

<nav class="tabs">
	{if !$pos_session.closed}
		{linkbutton href="tab.php?session=%d"|args:$pos_session.id label="Retour à l'encaissement" shape="left"}
		{linkbutton href="session_close.php?id=%d"|args:$pos_session.id label="Clôturer la caisse" shape="delete"}
	{else}
		{linkbutton href="./" label="Retour" shape="left"}
		{linkbutton href="%s&pdf=1"|args:$self_url label="Télécharger en PDF" shape="print"}
		{if $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_ADMIN) && !$pos_session->isSynced()}
			{linkbutton href="session_delete.php?id=%d"|args:$pos_session.id label="Supprimer cette session de caisse" shape="delete"}
		{/if}
	{/if}

	{if !$_GET.details}
		{linkbutton href="%s&details=1"|args:$self_url label="Afficher les détails des notes" shape="eye"}
	{else}
		{linkbutton href="%s?id=%d"|args:$self_url_no_qs,$pos_session.id label="Cacher les détails des notes" shape="eye-off"}
	{/if}

</nav>

{if $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_READ) && $pos_session.closed}
	{form_errors}
	{if $transaction}
		<p class="block confirm">
			Cette session de caisse est enregistrée dans la comptabilité :
			{link class="num" href="!acc/transactions/details.php?id=%d"|args:$transaction.id label="#%d"|args:$transaction.id}
		</p>
	{else}
		<form method="post" action="">
			<p class="block alert">
				Cette session de caisse n'est pas enregistrée dans la comptabilité.
				{if $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_WRITE) && isset($csrf_key)}
				{csrf_field key=$csrf_key}
				<br />{button type="submit" name="sync" label="Créer l'écriture" shape="right" class="main"}
				{/if}
			</p>
		</form>
	{/if}
{/if}

{$export|raw}

{include file="_foot.tpl"}