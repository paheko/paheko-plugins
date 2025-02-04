{include file="_head.tpl" title=$plugin.label}

<nav class="tabs">
	<ul>
		<li class="current"><a>Accueil</a></li>
		<li><a href="config.php">Configuration</a></li>
	</ul>
</nav>

<nav class="home">
	<ul>
		<li><a class="icn-btn" href="{$admin_url}acc/transactions/new.php?t=1&ab={$account_code}">{icon shape="money"}<span>Recevoir un paiement</span></a></li>
	</ul>
</nav>

<fieldset>
	<legend>Obtenir un lien d'inscription</legend>
	<dl>
		{input type="select" name="id_service" options=$services label="Activité" required=1}
		<dt><label for="f_fees">Tarifs disponibles :</label></dt>
		{foreach from=$fees item="fee"}
		{input type="checkbox" class="service s%s"|args:$fee->id_service name="fees[]" label="%s (%s€)"|args:$fee->label,$fee->amount/100 value=$fee->id default=$fee->id}
		{/foreach}
	</dl>
	<div>
		{button id="copy-link" label="Copier l'URL"}
		{button id="copy-code" label="Copier le code d'intégration (iframe)"}
		{linkbutton id="open" href="#" target="blank" label="Ouvrir"}
	</div>
	<p class="help block">Conseil : raccourcissez l'URL en utilisant un outil tel que <a href="https://ouvaton.link/">ouvaton.link</a></p>
</fieldset>

<script type="text/javascript">
const baseUrl = "{$base_url}";

{literal}
function selectChoice() {
	let choice = $('#f_id_service').value;
	g.toggle('dd:has(> input.service)', false);
	g.toggle('dd:has(> input.service.s' + choice + ')', true);
}
$('#f_id_service').onchange = selectChoice;
selectChoice();

function getUrl() {
	const serviceId = $('#f_id_service').value;
	const inputs = $('dd:not(.hidden) > input.service');
	var feesStr = ""
	for(var i in inputs) {
		const el = inputs[i];
		if(el.checked) feesStr += "&fees[]=" + el.value
	}
	return `${baseUrl}register.php?service_id=${serviceId}${feesStr}`
}
$('#open').onmousedown = () => $('#open').setAttribute('href', getUrl());
$('#copy-link').onclick = () => navigator.clipboard.writeText(getUrl());
$('#copy-code').onclick = () => navigator.clipboard.writeText('<iframe src=' + getUrl() + ' frameborder=0 style="width: 100%;height: 2000px;max-width: 100%;"></iframe>');
{/literal}
</script>

{*
<fieldset>
	<legend>Liens des formulaires d'inscription</legend>
	<dl>
		{foreach from=$services item="service" key="id"}
		<span>{$service} :</span> <a href="{$plugin_url}register.php?service_id={$id}">{$plugin_url}register.php?service_id={$id}</a>
		{/foreach}
	</dl>
</fieldset>
*}

{include file="_foot.tpl"}