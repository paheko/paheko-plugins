{include file="admin/_head.tpl" title="Gestion produit" current="plugin_%s"|args:$plugin.id}

{form_errors}

<form method="post" action="{$self_url}" data-focus="1">
	<fieldset>
		<legend>Modifier un produit</legend>
		<dl>
			{input type="select" name="category" label="Catégorie" required="true" source=$product options=$categories}
			{input type="text" name="name" label="Nom" required="true" source=$product}
			{input type="textarea" name="description" label="Description" source=$product}
			{input type="money" name="price" label="Prix unitaire" source=$product required=true help="Indiquer un montant négatif pour une sortie de la caisse (par exemple un remboursement)."}
			{input type="number" name="qty" label="Quantité" help="Quantité par défaut quand le produit est ajouté à une note" source=$product required=true}
			{input type="number" name="stock" label="Stock" help="Stock actuel du produit, celui-ci sera décrémenté à chaque clôture de caisse. Ne modifier que si vous faites un inventaire. Laisser vide pour les produits non-stockables (adhésions, services, etc.)." source=$product}
			<dt>Moyens de paiement</dt>
			{foreach from=$methods item="method"}
				{input type="checkbox" name="methods[%s]"|args:$method.id label=$method.name value="1" default=$method.checked}
			{/foreach}
			<dd class="help">
				Décocher tous les moyens de paiement pour que le produit ne puisse plus être ajouté aux notes.
			</dd>
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

{include file="admin/_foot.tpl"}