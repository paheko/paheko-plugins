{include file="_head.tpl" title="Modifier un produit"}

{form_errors}

<form method="post" action="{$self_url}" data-focus="1">
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
			{input type="number" name="stock" label="Stock" help="Nombre de produits dans le stock à cet instant. Celui-ci sera décrémenté à chaque clôture de caisse. Ne modifier que si vous faites un inventaire. Laisser vide pour les produits non-stockables (adhésions, services, etc.)." source=$product}
			{input type="money" name="purchase_price" label="Prix d'achat unitaire" source=$product required=false help="Indiquer ici le prix d'achat, si le produit a été acheté. Ce prix est utilisé pour calculer la valeur du stock lors de l'inventaire."}
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
}

checkWeightRequired();
c.onchange = checkWeightRequired;
</script>
{/literal}

{include file="_foot.tpl"}