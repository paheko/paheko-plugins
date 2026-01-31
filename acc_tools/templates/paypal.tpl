{include file="_head.tpl" title="Conversion PayPal"}

{form_errors}

<p>{linkbutton href="./" label="Retour" shape="left"}</p>

<div class="block help">
	<h4>Comment récupérer le fichier CSV de PayPal ?</h4>
	<ul>
		<li>Connectez-vous sur le site de Paypal</li>
		<li>Cliquez ici : {linkbutton href="https://www.paypal.com/reports/dlog" target="_blank" label="Créer un rapport personnalisé"}<br /><small>(Sinon, aller dans le menu "Activité" &rarr; "Tous les rapports" &rarr; "Activités" &rarr; "Rapports d'activité")</small></li>
		<li>Indiquez "Impact sur le solde" pour "Type de transaction"</li>
		<li>Indiquez "CSV" pour le "Format"</li>
		<li>Sélectionnez la période désirée et cliquez sur "Créer le rapport"</li>
	</ul>
	<p>Patientez ensuite, et téléchargez le fichier CSV sur votre ordinateur. Vous pourrez ensuite utiliser ce fichier CSV ci-dessous.</p>
</div>

<form method="post" action="" enctype="multipart/form-data" data-disable-progress="1">
	<fieldset>
		<legend>Charger un fichier CSV PayPal</legend>
		<dl>
			{input type="file" name="csv" required=true label="Fichier CSV" accept="'.csv,text/csv,application/csv,.CSV"}
			{input type="radio" name="group_fees" value=1 default=1 label="Regrouper toutes les commissions en une seule écriture"}
			{input type="radio" name="group_fees" value=0 label="Créer une écriture pour chaque commission prélevée"}
		</dl>
		<p class="submit">
			{csrf_field key=$csrf_key}
			{button type="submit" label="Charger" shape="right" class="main" name="load"}
		</p>
	</fieldset>
</form>

{include file="_foot.tpl"}
