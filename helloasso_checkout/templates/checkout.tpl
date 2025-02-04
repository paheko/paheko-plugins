{include file="_head.tpl" title="Paiement"}

<div style="text-align: center">
	{if $_GET.status == 'success'}
		<h2>Paiement enregistré. Vous pouvez fermer cette page.</h2>
	{elseif $_GET.status == 'canceled'}
		<h2>Le paiement n'a pas été jusqu'au bout. Vous n'évez pas été débité.</h2>
		<br>
		{linkbutton href=$checkout_url label="Relancer le paiement" shape="right" class="main"}
	{elseif isset($checkout_url)}
		<h2>Montrez le QR code au client afin qu'il le scanne :</h2>
		<br>
		<img src="{$qr_code_src}&data={$checkout_url}" style="margin-bottom: 20px"/>
		<div style="margin-bottom: 200px">
			{button id="copy" label="Copier l'URL"}
			{button id="share" label="Partager l'URL"}
		</div>
	{else}
		<h2>Cliquez sur le QR code pour générer le lien de paiement HelloAsso :</h2>
		<br>
		<a href="?_dialog&transaction_id={$_GET.transaction_id}&status=new"><img src="{$qr_code_src}&data=example" style="opacity: 0.25" /></a>
	{/if}
</div>

<script>
const url = "{$checkout_url}";
{literal}
$('#copy').onclick = () => navigator.clipboard.writeText(url);
$('#share').onclick = () => navigator.share({url});
{/literal}
</script>


{include file="_foot.tpl"}