{include file="_head.tpl" title="Raccourci"}

{form_errors}

<form method="post" action="{$self_url}" enctype="multipart/form-data">

	<fieldset>
		<legend>{if $shortcut->exists()}Modifier un raccourci{else}Ajouter un raccourci{/if}</legend>
		<dl>
			{input type="text" name="label" source=$shortcut label="Libellé" required=true}
			{input type="url" name="url" source=$shortcut label="Adresse URL" required=true}
			{input type="checkbox" name="home" value="1" source=$shortcut label="Afficher en bouton sur la page d'accueil"}
			{input type="checkbox" name="menu" value="1" source=$shortcut label="Afficher dans le menu"}
			{input type="checkbox" name="iframe" value="1" source=$shortcut label="Afficher dans un cadre intégré"}
			<dd class="help">
				Si coché, le raccourci s'ouvrira dans Paheko.
				Sinon le raccourci s'ouvrira dans un nouvel onglet.<br/>
				Note : la plupart des sites bloquent ce réglage, et les cookies sont séparés, donc par exemple une connexion avec mot de passe à l'intérieur du cadre peut ne pas fonctionner.
			</dd>
		</dl>
	</fieldset>

	<fieldset class="shapes">
		<legend>Icône</legend>
		{if $shortcut.icon}
			<p class="custom-icon"><img src="{$shortcut->getIconURL()}" alt="" /></p>
			<dl>
				{input type="checkbox" name="delete_icon" value=1 label="Supprimer l'icône personnalisée"}
			</dl>
		{else}
			<p class="help">Sélectionner une icône pour le bouton sur la page d'accueil.</p>
			<ul>
				{foreach from=$shapes item="name"}
				<li><label><input type="radio" name="shape" value="{$name}"{if $name === $shortcut.shape} checked="checked"{/if} />{icon shape=$name}</label></li>
				{/foreach}
			</ul>
			<dl class="custom-icon">
				{input type="file" name="icon" label="Ou sélectionnez une image personnalisée à utiliser comme icône" accept="image+svg"}
			</dl>
		{/if}
	</fieldset>

	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" class="main" name="save" label="Enregistrer" shape="right"}
	</p>
</form>

{literal}
<style type="text/css">
.shapes ul {
	display: flex;
	flex-wrap: wrap;
	margin: .5em 1em;
	font-size: 2em;
}
.shapes ul label {
	padding: .2em;
	border-radius: .2em;
	border: 2px solid transparent;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 1.5em;
	height: 1.5em;
}
.shapes ul label:has(:checked) {
	border-color: darkorange;
	background: hsl(from orange h s l / 0.2);
}
.shapes ul input {
	display: block;
	width: 0;
	height: 0;
	overflow: hidden;
}
.shapes span, .shapes span::before {
	display: inline-block;
	vertical-align: middle;
}
.shapes p.custom-icon img {
	max-width: 100px;
	max-height: 100px;
}
</style>
{/literal}

{include file="_foot.tpl"}
