{include file="_head.tpl" title="Catégories de l'agenda" current="plugin_pim" hide_title=true plugin_css=['calendar.css']}

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

<dl class="large">
	<dt>Connexion CardDAV/CalDAV</dt>
	<dd class="help">
		Certains logiciels comme Vivaldi, Thunderbird, DAVx5 (Android), etc. permettent de synchroniser agendas et contacts.
	</dd>
	<dd>
		Utilisez l'adresse suivante pour configurer ces logiciels :<br />
		{input type="text" readonly=true default=$dav_url copy=true name=""}
		<p class="block alert">
			Cette fonctionnalité n'ayant pas été testée avec tous les logiciels, des bugs peuvent mener à la perte de données.
		</p>
	</dd>
	<dd class="help">
		Utilisez votre identifiant et mot de passe habituel pour vous connecter.
	</dd>
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

{include file="_foot.tpl"}
