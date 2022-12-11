{include file="_head.tpl" title="Modifier le vélo n°%d"|args:$velo.id current="plugin_%s"|args:$plugin.id}

{include file="%s_nav.tpl"|args:$plugin_tpl current=""}

{include file="%s_form.tpl"|args:$plugin_tpl}

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