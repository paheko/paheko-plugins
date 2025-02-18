{include file="_head.tpl" title="Configuration" current="plugin_pim" hide_title=true plugin_css=['calendar.css']}

<nav class="tabs">
	<ul>
		<li><a href="../">Agenda</a></li>
		<li><a href="../contacts/">Contacts</a></li>
		<li class="current"><a href="./">Configuration</a></li>
	</ul>
	<ul class="sub">
		<li><a href="categories/">Catégories de l'agenda</a></li>
	</ul>
</nav>

<form method="post" action="">
<dl class="large">
	<dt>Connexion CardDAV/CalDAV</dt>
	<dd class="help">
		Certains logiciels comme Vivaldi, Thunderbird, DAVx5 (Android), etc. permettent de synchroniser agendas et contacts.
	</dd>
	{if $dav}
		<dd>
			Utilisez l'adresse suivante pour configurer ces logiciels :<br />
			{input type="text" readonly=true default=$dav.url copy=true name=""}
			<p class="block alert">
				Cette fonctionnalité n'ayant pas été testée avec tous les logiciels, des bugs peuvent mener à la perte de données.
			</p>
		</dd>
		<dd>
			Nom d'utilisateur&nbsp;:<br />
			{input type="text" readonly=true default=$dav.login copy=true name=""}<br />
			{if $dav.password}
				Mot de passe&nbsp;:<br />
				{input type="text" readonly=true default=$dav.password copy=true name=""}
			{else}
				{button type="submit" name="generate" label="Générer un nouveau mot de passe" value=1 shape="reload"}
			{/if}
		</dd>
	{else}
		<dd>
			{button type="submit" name="generate" label="Générer mon mot de passe" value=1 shape="reload"}
		</dd>
		<dd class="help">
			Cliquez sur ce bouton pour générer un nom d'utilisateur et mot de passe dédiés aux logiciels tiers, protégeant ainsi votre mot de passe principal.
		</dd>
	{/if}
	<dt>Import</dt>
	<dd class="help">
		Pour importer vos données existantes depuis un fichier iCalendar ou VCF.
	</dd>
	<dd>
		{linkbutton href="../upload.php" label="Importer événements et agendas" shape="calendar" target="_dialog"}<br />
		{linkbutton href="../contacts/upload.php" label="Importer contacts" shape="users" target="_dialog"}<br />
	</dd>
	<dt>Export</dt>
	<dd class="help">
		Pour exporter vos données vers un fichier iCalendar ou VCF.
	</dd>
	<dd>
		{linkbutton href="categories/?export=all" label="Exporter tous les agendas" shape="calendar"}<br />
		{linkbutton href="../contacts/export.php" label="Exporter tous les contacts" shape="users"}<br />
	</dd>
</dl>
</form>

{include file="_foot.tpl"}
