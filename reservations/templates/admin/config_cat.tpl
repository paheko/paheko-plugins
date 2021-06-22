{include file="admin/_head.tpl" title="Configuration — %s"|args:$plugin.nom current="plugin_%s"|args:$plugin.id}

{include file="%s/templates/admin/_menu.tpl"|args:$plugin_root current="config"}

{form_errors}

<form method="post" action="{$self_url}">

	<fieldset>
		<legend>Modifier le type de créneau</legend>
		<dl>
			<dt><label for="f_nom">Nom</label> <b title="(Champ obligatoire)">obligatoire</b></dt>
			<dd><input type="text" name="nom" id="f_nom" value="{form_field name="nom" data=$category}" required="required" /></dd>
			<dt><label for="f_introduction">Texte d'introduction</label> à afficher sur la page de choix de type de créneau</dt>
			<dd class="help">Syntaxe <a href="{$admin_url}web/_syntaxe.html" target="_blank">SkrivML</a> acceptée</dd>
			<dd class="help">Ne sera affiché que si plusieurs types de créneaux existent.</dd>
			<dd><textarea name="introduction" id="f_introduction" cols="70" rows="3">{form_field name="introduction" data=$category}</textarea></dd>
			<dt><label for="f_description">Texte de présentation</label> à afficher sur la page de réservation</dt>
			<dd class="help">Syntaxe <a href="{$admin_url}web/_syntaxe.html" target="_blank">SkrivML</a> acceptée</dd>
			<dd><textarea name="description" id="f_description" cols="70" rows="15">{form_field name="description" data=$category}</textarea></dd>
		</dl>
	</fieldset>

	<fieldset>
		<legend>Information supplémentaire</legend>
		<dl class="champ">

			<dt><input type="checkbox" name="champ_actif" value="1" {if $category.champ}checked="checked"{/if} id="f_champ_actif" /> <label for="f_champ_actif">Demander une information supplémentaire</label></dt>
			<dd class="help">Dans tous les cas le formulaire de réservation demande de renseigner un nom, mais il est possible de demander une information supplémentaire à la personne qui fait la réservation.</dd>
			<dt><label for="f_title">Titre</label> <b title="(Champ obligatoire)">obligatoire</b></dt>
			<dd><input type="text" name="champ[title]" id="f_title" value="{form_field data=$category.champ name=title}" size="60" /></dd>
			<dt><label for="f_help">Aide</label></dt>
			<dd class="help">Texte d'aide qui apparaîtra en dessous du champ à renseigner</dd>
			<dd><input type="text" name="champ[help]" id="f_help" value="{form_field data=$category.champ name=help}" size="100" /></dd>
			<dt><input type="checkbox" id="f_mandatory" name="champ[mandatory]" value="1" {form_field data=$category.champ name=mandatory checked="1"} /> <label for="f_mandatory">Champ obligatoire</label></dt>
			<dd class="help">Si coché, le champ ne pourra pas être laissé vide.</dd>

		</dl>
	</fieldset>

	<p class="submit">
		{csrf_field key="config_plugin_%s"|args:$plugin.id}
        {button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
	</p>

</form>

<form method="post" action="{$self_url}">
    <fieldset>
        <legend>Supprimer ce type de créneau</legend>
        <p class="alert block">Ceci effacera toutes les réservations liées.</p>
        <p class="submit">
            {csrf_field key="config_plugin_%s"|args:$plugin.id}
            <input type="submit" name="delete" value="Supprimer &rarr;" />
        </p>
    </fieldset>
</form>

<script type="text/javascript">
var champ_identifiant = "f_{$config.champ_identifiant|escape:'js'}";
var champ_identite = "f_{$config.champ_identite|escape:'js'}";

{literal}
(function () {
    if (!document.querySelector || !document.querySelectorAll)
    {
        return false;
    }

    var field = document.querySelector('.champ');

    if (field.querySelector('.options'))
    {
        var options = field.querySelectorAll('.options li');
        var options_nb = options.length;

        if (options[0].parentNode.tagName.toLowerCase() == 'ul')
        {
            // champ select
            for (j = 0; j < options_nb; j++)
            {
                var remove = document.createElement('input');
                remove.type = 'button';
                remove.className = 'icn';
                remove.value = '-';
                remove.title = 'Enlever cette option';
                remove.onclick = function (e) {
                    var p = this.parentNode;
                    p.parentNode.removeChild(p);
                };
                options[j].appendChild(remove);
            }
        }

        var add = document.createElement('input');
        add.type = 'button';
        add.className = 'icn add';
        add.value = '+';
        add.title = 'Ajouter une option';
        add.onclick = function (e) {
            var p = this.parentNode.parentNode;
            var options = p.querySelectorAll('li');
            var new_option = this.parentNode.cloneNode(true);
            var btn = new_option.querySelector('input.add');
            new_option.getElementsByTagName('input')[0].value = '';

            if (options.length >= 30)
            {
                new_option.removeChild(btn);
            }
            else
            {
                btn.onclick = this.onclick;
            }

            p.appendChild(new_option);
            this.parentNode.removeChild(this);
        };

        options[options_nb - 1].appendChild(add);
    }
}());
{/literal}
</script>

{include file="admin/_foot.tpl"}
