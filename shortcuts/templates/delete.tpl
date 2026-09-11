{include file="_head.tpl" title="Supprimer un raccourci"}

{include file="common/delete_form.tpl"
	legend="Supprimer un raccourci"
	warning="Supprimer le raccourci « %s » ?"|args:$shortcut.label
}

{include file="_foot.tpl"}
