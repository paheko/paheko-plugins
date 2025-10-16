{include file="_head.tpl" title="Modifier un produit"}

{form_errors}

<form method="post" action="{$self_url}">
	<aside class="secondary">
		<fieldset>
			<legend>Moyens de paiement</legend>
			<dl>
				{foreach from=$methods item="method"}
					{input type="checkbox" name="methods[%s]"|args:$method.id label=$method.name value="1" default=$method.checked}
				{/foreach}
				<dd class="help">
					Décocher tous les moyens de paiement pour que le produit ne puisse plus être ajouté aux notes.
				</dd>
			</dl>
		</fieldset>

		<fieldset>
			<legend>Lier à une activité</legend>
			<dl>
				{input type="select_groups" name="id_fee" source=$product default_empty="— Ne pas lier —" options=$fees label="Tarif"}
				<dd class="help">
					Sélectionner un tarif d'activité ici pour générer une inscription à ce tarif lors de la clôture de la caisse.
				</dd>
			</dl>
		</fieldset>

		<div class="fee-only alert block">
			<p><strong>Attention&nbsp;:</strong></p>
			<ul>
				<li>si la note de caisse n'est pas liée à un membre existant, il faudra déjà créer le membre pour pouvoir clôturer la caisse&nbsp;;</li>
				<li>le compte de la catégorie de produits sera utilisé pour la comptabilité&nbsp;;</li>
				<li>l'inscription ne sera enregistrée qu'à la clôture de la caisse&nbsp;;</li>
				<li>l'inscription ne sera pas liée à une écriture comptable&nbsp;;</li>
				<li>il ne sera pas possible d'encaisser plusieurs adhésions sur la même note de caisse.</li>
			</ul>
		</div>

		<fieldset class="fee-hidden">
			<legend>Poids</legend>
			<dl>
				<?php
				$weight_based_price = $product->weight === $product::WEIGHT_BASED_PRICE;
				$weight_required = $product->weight === $product::WEIGHT_REQUIRED || $weight_based_price;
				$weight = $product->weight > 0 ? $product->weight : 0;
				?>
				{input type="checkbox" name="weight_required" value=1 default=$weight_required label="Demander le poids du produit"}
				<dd class="help">Si cette case est cochée, la personne utilisant la caisse devra saisir un poids lors de l'ajout de ce produit dans une note de caisse.</dd>
			</dl>
			<dl class="price-weight">
				{input type="checkbox" name="weight_based_price" value=1 default=$weight_based_price label="Le prix du produit est basé sur le poids"}
				<dd class="help">Cocher cette case pour vendre un produit au poids (par exemple des légumes). Dans ce cas le prix unitaire indiqué dans cette fiche vaudra pour 1 kg.</dd>
			</dl>
			<dl class="weight">
				{input type="weight" name="weight" label="Poids unitaire" default=$weight required=false help="Indiquer ici le poids du produit, en kilogrammes. Ce poids est utilisé pour calculer la quantité de marchandises vendues."}
			</dl>
		</fieldset>

	</aside>

	<fieldset>
		<legend>Modifier un produit</legend>
		<dl>
			{input type="select" name="category" label="Catégorie" required="true" source=$product options=$categories}
			{input type="text" name="name" label="Nom" required="true" source=$product}
			{input type="textarea" name="description" label="Description" source=$product}
			{input type="money" name="price" label="Prix unitaire" source=$product required=true help="Indiquer zéro pour un produit gratuit. Indiquer un montant négatif pour une sortie de la caisse (par exemple un remboursement)."}
			{input type="number" name="qty" label="Quantité" help="Quantité par défaut quand le produit est ajouté à une note" source=$product required=true}
			{input type="text" name="code" label="Numéro de code barre" required=false source=$product help="Si ce champ est rempli avec le code barre à 13 chiffres du produit, il sera possible d'utiliser ce code barre pour retrouver un produit lors de l'encaissement. Cela permet également d'utiliser une douchette."}
			<?=$product->getSVGBarcode();?>
		</dl>
		<dl class="fee-hidden">
			{input type="number" name="stock" label="Stock" help="Nombre de produits dans le stock à cet instant. Celui-ci sera décrémenté à chaque clôture de caisse. Ne modifier que si vous faites un inventaire. Laisser vide pour les produits non-stockables (adhésions, services, etc.)." source=$product}
			{input type="money" name="purchase_price" label="Prix d'achat unitaire" source=$product required=false help="Indiquer ici le prix d'achat, si le produit a été acheté. Ce prix est utilisé pour calculer la valeur du stock lors de l'inventaire."}
		</dl>
		<dl>
			{if !$product->isLinked()}
				{input type="list" name="linked_products" target="list_for_linking.php?id=%d"|args:$product.id multiple=true label="Produits associés" required=false default=$linked_products}
				<dd class="help">
					Chaque produit indiqué ici sera automatiquement ajouté à la note lors de l'ajout de ce produit.<br />
					Les produits liés ne pourront être supprimés de la note sans supprimer le produit "parent".<br />
					Particulièrement utile pour les adhésions qui comprennent une part fédérale par exemple.
				</dd>
			{/if}
			<dt>Archivage</dt>
			{input type="checkbox" name="archived" label="Produit archivé" source=$product value=1}
			<dd class="help">Si coché, ce produit ne sera plus proposé à la vente.</dd>
		</dl>
	</fieldset>

	<p class="help">
		Toute modification dans cette fiche n'affectera pas les notes en cours ou clôturées.
	</p>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
	</p>
</form>

{literal}
<script type="text/javascript">
var c = $('#f_weight_required_1');
function checkWeightRequired() {
	g.toggle('.weight', !c.checked);
	g.toggle('.price-weight', c.checked);
	if (!c.checked) {
		$('#f_weight_based_price_1').checked = false;
	}
}

checkWeightRequired();
c.onchange = checkWeightRequired;

var fee = $('#f_id_fee');
function changeFee() {
	g.toggle('.fee-only', !!fee.value);
	g.toggle('.fee-hidden', !fee.value);
}
changeFee();
fee.onchange = changeFee;
</script>
{/literal}

{include file="_foot.tpl"}