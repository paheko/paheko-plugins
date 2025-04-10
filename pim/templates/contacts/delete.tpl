{include file="_head.tpl" title="Supprimer un contact" current="plugin_pim" }

{include file="common/delete_form.tpl"
	legend="Supprimer ce contact ?"
	warning="Êtes-vous sûr de vouloir supprimer le contact « %s » ?"|args:$contact->getFullName()}

{include file="_foot.tpl"}