{include file="_head.tpl" title=$service->label}

{form_errors}

{if $_GET.status == 'success'}
	<h2>Inscription enregistrée. Vous pouvez fermer cette page.</h2>
{elseif $_GET.step == 2}
	<form id="form" method="post" action="{$self_url}">

		{input type="hidden" name="id" default=$user->id}

		<fieldset>
			<legend>Informations personnelles</legend>
			<dl>
				{foreach from=$fields item="field"}
					{if $field.name == 'email'}
						{input type="email" name=$field.name value=$field.value label=$field.label readonly=true}
					{else}
						{edit_user_field field=$field user=$user}
					{/if}
				{/foreach}
			</dl>
		</fieldset>

		<fieldset>
			<legend>Paiement</legend>
			<dt>Choisissez un tarif :</dt>
			{foreach from=$fees item='fee' }
				{input type="radio" name="fee" required=1 value=$fee.id label='%s (%d€)'|args:$fee.label:$fee.amount/100}
			{/foreach}
		</fieldset>

		<p class="submit">
			{csrf_field key=$csrf_key}
			{if $status == 'checkout'}
				<a id="helloasso-btn" href={$checkout.url} target="_dialog" style="display: none"></a>
				<input type="hidden" name="checkout_id" value={$checkout.id}></input>
				<script src="./script.js"></script>
				<button id="submit-btn" type="submit" style="display: none">

				<script src="./script.js"></script>
			{/if}
			{button type="submit" name="validate" label="Procéder au paiement" shape="right" class="main"}
		</p>
	</form>

	{if $_GET.status == 'error'}
		<p class="block error">
			Le paiement a échoué. Vous n'avez pas été débité. Si besoin, contactez l'association.
		</p>
	{elseif $_GET.status == 'canceled'}
		<p class="block error">
			Le paiement n'a pas été jusqu'au bout. Vous n'évez pas été débité.
		</p>
	{/if}
{else}
	<form id="form" method="post" action="{$self_url}&step=2">
		<fieldset>
			{input type="email" name="email" required=1 label="Votre adresse e-mail" help="Si vous avez déjà adhéré(e) précédemment, merci de renseigner la même adresse e-mail pour éviter les doublons."}
			{button type="submit" name="check" label="Continuer" shape="right" class="main"}
		</fieldset>
	</form>
{/if}

{include file="_foot.tpl"}