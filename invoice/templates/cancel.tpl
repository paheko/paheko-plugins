{include file="_head.tpl" title=$question current="plugin_invoice"}

{form_errors}

{include file="common/delete_form.tpl"
	legend=$question
	warning=$question
	info=$details
	button_label="Annuler"
}

{include file="_foot.tpl"}
