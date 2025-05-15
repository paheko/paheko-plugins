{include file="_head.tpl" title="Supprimer un vélo"}

{include file="./_nav.tpl" current=""}

{include file="common/delete_form.tpl"
	legend="Supprimer ce vélo ?"
	warning="Êtes-vous sûr de vouloir supprimer le vélo n°%s ?"|args:$velo.id
}

{include file="_foot.tpl"}