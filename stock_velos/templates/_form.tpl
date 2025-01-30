<form method="post" action="{$self_url}">

{form_errors}

<fieldset>
	<legend>Général</legend>
	<dl>
		<dt>Numéro unique du vélo</dt>
		<dd>(Sera donné automatiquement à l'enregistrement du vélo.)</dd>
		{if $fields.etiquette.enabled}
			{input type="number" name="etiquette" label="Numéro étiquette" required=$fields.etiquette.required source=$velo}
			{if !$velo.id}
			<dd>
				<input type="button" onclick="document.getElementById('f_etiquette').value='{$libre|escape}';" value="Utiliser la première étiquette libre (n°{$libre|escape})" />
			</dd>
			{/if}
		{/if}
		{if $fields.bicycode.enabled}
			{input type="text" name="bicycode" label="Bicycode" source=$velo required=$fields.bicycode.required}
		{/if}
		{if $fields.prix.enabled}
			{input type="money" label="Prix du vélo" name="prix" required=$fields.prix.required min="-1" step="1" source=$velo}
			<dd>
				<input type="button" onclick="document.getElementById('f_prix').value='-1';" value="Vélo à démonter" />
				<input type="button" onclick="document.getElementById('f_prix').value='0';" value="Pas en vente" />
			</dd>
		{/if}
		{if $fields.notes.enabled}
			{input type="textarea" name="notes" label="Notes" source=$velo}
		{/if}
	</dl>
</fieldset>

{if $fields.source.enabled || $fields.source_details.enabled}
<fieldset>
	<legend>Provenance</legend>
	<dl>
	{if $fields.notes.enabled}
		{input type="select" name="source" label="D'où provient le vélo ?" required=$fields.source.required options=$fields.source.options default="Don" source=$velo}
	{/if}
	{if $fields.source_details.enabled}
		{input type="text" name="source_details" required=$fields.source_details.required label="Détails sur la provenance" help="pour le don : numéro d'adhérent ou nom du donneur" required=true source=$velo datalist=$fields.source_details.options}
	{/if}
	</dl>
</fieldset>
{/if}

{if $fields.type.enabled
	|| $fields.roues.enabled
	|| $fields.genre.enabled
	|| $fields.couleur.enabled
	|| $fields.modele.enabled}
<fieldset>
	<legend>Description du vélo</legend>
	<dl>
	{if $fields.type.enabled}
		{input type="select" name="type" required=$fields.type.required label="Type" options=$fields.type.options source=$velo}
	{/if}
	{if $fields.roues.enabled}
		{input type="select" name="roues" required=$fields.roues.required label="Taille" options=$fields.roues.options source=$velo}
	{/if}
	{if $fields.genre.enabled}
		{input type="select" name="genre" required=$fields.genre.required label="Genre de cadre" options=$fields.genre.options source=$velo}
	{/if}
	{if $fields.couleur.enabled}
		{input type="text" name="couleur" required=$fields.couleur.required label="Couleur" source=$velo}
	{/if}
	{if $fields.modele.enabled}
		{input type="text" name="modele" required=$fields.modele.required label="Marque et modèle" source=$velo}
	{/if}
	</dl>
</fieldset>
{/if}

{if $fields.poids.enabled}
	<fieldset>
		<legend>Poids du vélo</legend>
		<dl>
			{input type="weight" name="poids" required=$fields.poids.required label="Poids (en kg)" source=$velo}
			<dd>
				{foreach from=$abaques key="label" item="value"}
					<input type="button" onclick="document.getElementById('f_poids').value='{$value}';" value="{$label}" />
				{/foreach}
			</dd>
		</dl>
	</fieldset>
{/if}

{if $fields.date_entree.enabled || $fields.etat_entree.enabled}
	<fieldset>
		<legend>Entrée du vélo</legend>
		<dl>
		{if $fields.date_entree.enabled}
			{input type="date" label="Date d'entrée dans le stock" name="date_entree" required=$fields.date_entree.required default=$now source=$velo}
		{/if}
		{if $fields.etat_entree.enabled}
			{input type="text" label="État à l'entrée dans le stock" name="etat_entree" required=$fields.etat_entree.required source=$velo}
		{/if}
		</dl>
	</fieldset>
{/if}

{if $fields.date_sortie.enabled || $fields.raison_sortie.enabled || $fields.details_sortie.enabled}
	<fieldset>
		<legend>Sortie du vélo</legend>
		<dl>
			{input type="date" label="Date de sortie" name="date_sortie" source=$velo}
			{input type="select" label="Raison de sortie" name="raison_sortie" options=$fields.raison_sortie.options source=$velo}
			{input type="text" label="Détails de sortie" name="details_sortie" help="Inscrire le numéro d'adhérent en cas de vente" source=$velo}
		</dl>
	</fieldset>
{/if}

<p class="submit">
	{csrf_field key=$csrf_key}
	{button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
</p>
</form>

<script type="text/javascript">
var abaques = {$abaques|escape:'json'};
{literal}
(function () {
	var p = document.querySelector('#f_poids');
	if (!p) {
		return;
	}

	function setAbaque(value) {
		for (var label in abaques) {
			if (!abaques.hasOwnProperty(label)) {
				continue;
			}

			if (value.match(new RegExp(label, 'i'))) {
				p.value = abaques[label];
			}
		}
	}

	document.querySelectorAll('#f_genre, #f_type, #f_roues').forEach((e) => { e.onchange = () => setAbaque(e.value); });
})();
{/literal}
</script>