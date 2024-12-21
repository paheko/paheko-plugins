{include file="_head.tpl" current="plugin_discuss}

<form method="post" action="">
<fieldset>
	<legend>Forum</legend>
	<dl>
		{input type="text" name="title" label="Nom du forum" required=true source=$forum}
		{input type="text" name="uri" label="Adresse unique" pattern="[a-z]([.\-]?[a-z0-9]+)*" required=true source=$forum}
		{input type="textarea" name="description" label="Description" source=$forum help="Peut contenir du Markdown"}
	</dl>
</fieldset>

<fieldset>
	<legend>Permissions</legend>
	<dl>
		{input type="radio-btn" name="subscribe_permission" prefix_required=true prefix_title="Qui peut s'abonnée ?" label="Tout le monde" value=$forum::OPEN source=$forum help="Une confirmation par e-mail sera toujours demandée."}
		{input type="radio-btn" name="subscribe_permission" label="Tout le monde, après validation" value=$forum::RESTRICTED source=$forum help="Chaque demande d'inscription devra être validée par un⋅e modo"}
		{input type="radio-btn" name="subscribe_permission" label="Personne" value=$forum::CLOSED source=$forum help="Seuls les modos pourront inscrire des abonné⋅e⋅s à ce forum."}

		{input type="radio-btn" name="post_permission" prefix_required=true prefix_title="Qui peut envoyer des messages ?" label="Tout le monde" value=$forum::OPEN source=$forum help="Même les personnes anonymes, non inscrites au forum, pourront poster des messages."}
		{input type="radio-btn" name="post_permission" label="Seulement les abonné⋅e⋅s du forum et les modos" value=$forum::RESTRICTED source=$forum}
		{input type="radio-btn" name="post_permission" label="Seulement les modos" value=$forum::CLOSED source=$forum}

		{input type="radio-btn" name="attachment_permission" prefix_required=true prefix_title="Qui peut envoyer des fichiers joints ?" label="Tout le monde" value=$forum::OPEN source=$forum help="Même les personnes anonymes, non inscrites au forum, pourront envoyer des fichiers joints."}
		{input type="radio-btn" name="attachment_permission" label="Seulement les abonné⋅e⋅s du forum et les modos" value=$forum::RESTRICTED source=$forum}
		{input type="radio-btn" name="attachment_permission" label="Seulement les modos" value=$forum::CLOSED source=$forum}

		{input type="radio-btn" name="archives_permission" prefix_required=true prefix_title="Qui peut lire les anciens messages ?" label="Tout le monde" value=$forum::OPEN source=$forum help="Même les personnes anonymes, non inscrites au forum, pourront lire les messages."}
		{input type="radio-btn" name="archives_permission" label="Seulement les abonné⋅e⋅s du forum et les modos" value=$forum::RESTRICTED source=$forum}
		{input type="radio-btn" name="archives_permission" label="Seulement les modos" value=$forum::CLOSED source=$forum}

		<dd class="help">Note : si seuls les modos peuvent inscrire des abonné⋅e⋅s, et que les anciens messages ne sont accessibles qu'aux modos, alors ce forum n'apparaîtra pas dans la liste des forums sur le site.</dd>
	</dl>
</fieldset>

<fieldset>
	<legend>Restrictions de fichiers joints</legend>
	<dl>
		{input type="checkbox" name="delete_forbidden_attachments" value=1 label="Limiter les type de fichiers joints autorisés" source=$forum}
		<dd class="help">Si cette case est cochée, seuls les fichiers suivants seront autorisés : {$types}.</dd>
		{input type="checkbox" name="resize_images" value=1 label="Redimensionner les images" source=$forum}
		<dd class="help">Si cette case est cochée, les images seront redimensionnées en taille maximale de 2048x2048, pour prendre moins de place (conseillé)</dd>
		{input type="number" min="0" max="25" step="0.1" suffix=" Mo" name="max_attachment_size" value=1 label="Taille maximale de fichier joint" source=$forum}
		<dd class="help">Si le redimensionnement d'images est activé, les images ne seront pas concernées, car leur taille finale sera réduite.</dd>
	</dl>
</fieldset>

{if $email_domain}
<fieldset>
	<legend>Envoi par e-mail</legend>
	<p class="help">
		Les participants à ce forum peuvent y participer en envoyant un e-mail à l'adresse [uri]@{$email_domain}
	</p>
	<dl>
		{input type="checkbox" name="disable_archives" value=1 label="Désactiver l'archivage des messages" source=$forum}
		<dd class="help">Si cette case est cochée, les messages seront simplement transmis aux membres du forum, sans être archivés.</dd>
		{input type="textarea" name="template_footer" label="Signature en bas des messages" source=$forum}
	</dl>
</fieldset>

	{if false && $can_encrypt}
	<fieldset>
		<legend>Sécurité et confidentialité des messages</legend>
		<p class="help">
			Les messages envoyés par e-mail à ce forum peuvent bénéficier d'un plus important degré de sécurité et de confidentialité avec PGP.
		</p>
		<p class="alert">
			Avertissement&nbsp;: ces réglages améliorent la confidentialité des échanges mais ne garantissent en rien vos échanges en cas d'attaque sophistiquée (par exemple par un État). En cas de piratage du serveur, il sera possible pour un attaquant d'envoyer des messages en se faisant pour le forum. Il sera également possible pour un acteur mal intentionné de déchiffrer les messages chez un des destinataires.
		</p>
		<dl>
			{input type="checkbox" name="verify_messages" value=1 label="Ne pas accepter de messages non signés" source=$forum}
			<dd class="help">Si cette case est cochée, les messages envoyés par e-mail devront être signés avec PGP, avec la clé secrète du membre, de préférence en chiffrant avec la clé publique du forum.</dd>
			{input type="checkbox" name="encrypt_messages" value=1 label="Chiffrer les messages" source=$forum}
			<dd class="help">Si cette case est cochée, les messages seront chiffrés par le forum en utilisant la clé publique du membre destinataire, et signés avec la clé secrète du forum.</dd>
		</dl>
	</fieldset>
	{/if}
{/if}

<fieldset>
	<legend>Modèles de messages</legend>
	<dl>
		{input type="textarea" name="template_welcome" label="Message de bienvenue" source=$forum cols=70 rows=10}
		{input type="textarea" name="template_goodbye" label="Message d'au-revoir" source=$forum cols=70 rows=7}
	</dl>
</fieldset>

<p class="submit">
	{button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
</p>

</form>

{include file="_foot.tpl"}