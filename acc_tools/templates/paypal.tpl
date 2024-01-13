{include file="_head.tpl" title="Conversion PayPal"}

{form_errors}

<p>{linkbutton href="./" label="Retour" shape="left"}</p>

<div class="block help">
	<h4>Comment récupérer le fichier CSV de PayPal ?</h4>
	<p>Rendez-vous sur le site de <a href="https://www.paypal.com/reports/dlog" target="_blank">PayPal</a>. Menu "Activité", puis "Tous les rapports". Dans cette page cliquez sur l'onglet "Activités", puis "Rapports d'activité".</p>
	<p>Dans "Type de transaction" indiquez "Impact sur le solde", dans "Format" indiquez "CSV". Sélectionnez la période désirée et cliquez sur "Créer le rapport".</p>
	<p>Patientez ensuite et téléchargez le fichier CSV sur votre ordinateur. Vous pourrez ensuite utiliser ce fichier CSV ci-dessous.</p>
</div>

<form method="post" action="" enctype="multipart/form-data" data-disable-progress="1">
	<fieldset>
		<legend>Charger un fichier CSV PayPal</legend>
		<dl>
			{input type="file" name="csv" required=true label="Fichier CSV"}
			{input type="radio" name="group_fees" value=1 default=1 label="Regrouper toutes les commissions en une seule écriture"}
			{input type="radio" name="group_fees" value=0 label="Créer une écriture pour chaque commission prélevée"}
		</dl>
		<p class="submit">
			{button type="submit" label="Charger" shape="right" class="main" name="load"}
		</p>
	</fieldset>
</form>

{include file="_foot.tpl"}
