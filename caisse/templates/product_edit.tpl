{include file="admin/_head.tpl" title="Gestion produit" current="plugin_%s"|args:$plugin.id}

<form method="post" action="{$self_url}">
	<fieldset>
		<legend>Modifier un produit</legend>
		<dl>
			{input type="select" name="category" label="Catégorie" required="true" source=$product options=$categories}
			{input type="text" name="name" label="Nom" required="true" source=$product}
			{input type="textarea" name="description" label="Description" source=$product}
			{input type="money" name="price" label="Prix unitaire" source=$product required=true}
			{input type="number" name="qty" label="Quantité" help="Quantité par défaut quand le produit est ajouté à une note" source=$product required=true}
			{input type="number" name="stock" label="Stock" help="Stock actuel du produit, celui-ci sera décrémenté à chaque clôture de caisse" source=$product}
			<dt>Moyens de paiement</dt>
			{foreach from=$methods item="method"}
				{input type="checkbox" name="methods[%s]"|args:$method.id label=$method.name value="1" default=$method.checked}
			{/foreach}
		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
	</p>
</form>

{include file="admin/_foot.tpl"}