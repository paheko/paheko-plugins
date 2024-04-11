<form method="post" action="{$self_url}">

{form_errors}

<fieldset>
	<legend>Général</legend>
	<dl>
		<dt>Numéro unique du vélo</dt>
		<dd>(Sera donné automatiquement à l'enregistrement du vélo.)</dd>
		<dt><label for="f_etiquette">Numéro étiquette</label> <b>(obligatoire)</b></dt>
		<dd>
			{input type="number" name="etiquette" required=true source=$velo}
			{if !$velo.id}
			<input type="button" onclick="document.getElementById('f_etiquette').value='{$libre|escape}';" value="Utiliser la première étiquette libre (n°{$libre|escape})" />
			{/if}
		</dd>
		{input type="text" name="bicycode" label="Bicycode" source=$velo}
		<dt><label for="f_prix">Prix du vélo</label></dt>
		<dd>
			{input type="number" name="prix" min="-1" step="1" source=$velo} €
			<input type="button" onclick="document.getElementById('f_prix').value='-1';" value="Vélo à démonter" />
			<input type="button" onclick="document.getElementById('f_prix').value='0';" value="Pas en vente" />
		</dd>
		{input type="textarea" name="notes" label="Notes" source=$velo}
	</dl>
</fieldset>

<fieldset>
	<legend>Provenance</legend>
	<dl>
		{input type="select" name="source" label="D'où provient le vélo ?" required=true options=$defaults.sources default="Don" source=$velo}
		{input type="text" name="source_details" label="Détails sur la provenance" help="pour le don : numéro d'adhérent ou nom du donneur" required=true source=$velo datalist=$defaults.sources_details}
	</dl>
</fieldset>

<fieldset>
	<legend>Description du vélo</legend>
	<dl>
		{input type="select" name="type" label="Type" required=true options=$defaults.types source=$velo}
		{input type="select" name="roues" label="Taille" required=true options=$defaults.tailles source=$velo}
		{input type="select" name="genre" label="Genre de cadre" required=true options=$defaults.genres source=$velo}
		{input type="text" name="couleur" label="Couleur" required=true source=$velo}
		{input type="text" name="modele" label="Marque et modèle" required=true source=$velo}
	</dl>
</fieldset>

<fieldset>
	<legend>Entrée du vélo</legend>
	<dl>
		{input type="date" label="Date d'entrée dans le stock" name="date_entree" required=true default=$now source=$velo}
		{input type="text" label="État à l'entrée dans le stock" name="etat_entree" required=false source=$velo}
	</dl>
</fieldset>

<fieldset>
	<legend>Sortie du vélo</legend>
	<dl>
		{input type="date" label="Date de sortie" name="date_sortie" source=$velo}
		{input type="select" label="Raison de sortie" name="raison_sortie" options=$defaults.raisons_sortie source=$velo}
		{input type="text" label="Détails de sortie" name="details_sortie" help="Inscrire le numéro d'adhérent en cas de vente" source=$velo}
	</dl>
</fieldset>

<p class="submit">
	{csrf_field key=$csrf_key}
	{button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
</p>
</form>