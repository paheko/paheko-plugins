{include file="_head.tpl" title="Conversion Mollie"}

{form_errors}

<p>{linkbutton href="./" label="Retour" shape="left"}</p>

<div class="block help">
	<p>Sur le site de Mollie, se rendre dans Rapports, Règlements, sélectionner l'année, puis avec la souris sélectionner le tableau et le copier (Ctrl+C)</p>
	<p>Coller (Ctrl+V) le tableau dans le champ ci-dessous et confirmer, l'outil générera un import simplifié Paheko avec les écritures de frais et de virement.</p>
</div>

<form method="post" action="" enctype="multipart/form-data" data-disable-progress="1">
	<fieldset>
		<legend>Charger des données Mollie</legend>
		<dl>
			<dt>Coller le tableau Mollie dans ce champ :</dt>
			<dd><div style="border: 2px solid #999; padding: .5rem; overflow: auto; width: 90%; height: 300px;" contenteditable oninput="$('#mollieTable').value = this.innerHTML;"></div></dd>
		</dl>
		<p class="submit">
			<input type="hidden" name="table" id="mollieTable" value="" />
			{csrf_field key=$csrf_key}
			{button type="submit" label="Charger" shape="right" class="main" name="load"}
		</p>
	</fieldset>
</form>

{include file="_foot.tpl"}
