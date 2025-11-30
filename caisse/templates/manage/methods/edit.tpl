{include file="_head.tpl" title="Moyen de paiement"}

{include file="../_nav.tpl" current='methods'}

{form_errors}

<form method="post" action="{$self_url}" data-focus="1">
	<fieldset>
		<legend>{if $method->exists()}Modifier un moyen de paiement{else}Nouveau moyen de paiement{/if}</legend>
		<dl>
			{input type="checkbox" name="enabled" value=1 label="Activer ce moyen de paiement" source=$method}
			<dd class="help">
				Si décoché, ce moyen de paiement ne sera pas utilisable dans la caisse.
			</dd>

			{input type="checkbox" name="is_default" value=1 label="Moyen de paiement par défaut" source=$method}
			<dd class="help">
				Si coché, ce moyen de paiement sera pré-selectionné pour chaque nouveau paiement dans la caisse.
			</dd>

			<dt>Type de paiement</dt>
			{input type="radio-btn" name="type" value=$method::TYPE_CASH label="Paiement informel (espèces, monnaie locale…)" source=$method}
			{input type="radio-btn" name="type" value=$method::TYPE_TRACKED label="Paiement suivi (chèques, carte bancaire…)" source=$method help="Une référence sera demandée pour chaque paiement avec ce moyen (par exemple : numéro de chèque), et il faudra valider chaque paiement lors de la clôture de la caisse, pour vérifier que le paiement n'a pas été égaré."}
			{input type="radio-btn" name="type" value=$method::TYPE_DEBT label="Ardoise (dette)" help="Le paiement sera noté comme étant une dette de l'usager l'égard de l'organisation. La dette pourra être réglée plus tard." source=$method}
			<dd class="help">Note : les écritures comptables de la caisse étant consolidées (regroupées), les ardoises de la caisse n'apparaissent pas comme dettes dans la comptabilité.</dd>

			{if count($locations)}
			{input type="select" name="id_location" options=$locations label="Lieu" required=true}
			{/if}

			{input type="text" name="name" label="Nom" required=true source=$method}

			{if !$method->exists()}
				{input type="checkbox" name="link_all" value=1 label="Accepter ce moyen de paiement pour tous les produits" default=1}
				<dd class="help">Si cette case est décochée, il faudra manuellement choisir quels produits peuvent être payés avec ce moyen de paiement.</dd>
			{/if}

			<?php $account = $method->account ? [$method->account => $method->account] : null; ?>
			{input required=true name="account" multiple=false target="!acc/charts/accounts/selector.php?key=code" type="list" label="Compte du plan comptable" default=$account help="Numéro du compte dans le plan comptable (par exemple 530 pour les espèces), utilisé pour intégrer les notes à la comptabilité."}
			{input type="money" name="min" label="Minimum" source=$method help="Si renseigné, ce moyen de paiement ne pourra pas être utilisé pour un paiement inférieur à ce montant."}
			{input type="money" name="max" label="Maximum" source=$method help="Si renseigné, ce moyen de paiement ne pourra pas être utilisé pour un paiement supérieur à ce montant."}

		</dl>
	</fieldset>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button type="submit" name="save" label="Enregistrer" shape="right" class="main"}
	</p>
</form>

{include file="_foot.tpl"}