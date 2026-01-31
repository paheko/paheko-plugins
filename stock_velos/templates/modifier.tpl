{include file="_head.tpl" title="Modifier le vélo n°%d"|args:$velo.id}

{include file="./_nav.tpl" current=""}

{include file="./_form.tpl"}

<script type="text/javascript">
{literal}
function fillSortieToday()
{
	var d = new Date();
	document.getElementById('f_date_sortie_d').value = d.getDate();
	document.getElementById('f_date_sortie_m').value = d.getMonth() + 1;
	document.getElementById('f_date_sortie_y').value = d.getFullYear();
}
{/literal}
</script>

{include file="_foot.tpl"}