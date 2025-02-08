{include file="_head.tpl" title="Supprimer un événement" current="plugin_pim" }

{include file="common/delete_form.tpl"
	legend="Supprimer cet événement ?"
	warning="Êtes-vous sûr de vouloir supprimer l'événement « %s » ?"|args:$event.title}

{include file="_foot.tpl"}