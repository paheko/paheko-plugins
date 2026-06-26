{include file="_head.tpl" title=$question current="plugin_invoice"}

{form_errors}

{include file="common/delete_form.tpl"
	legend=$question
	confirm_label=$question
	warning=$question
}

{include file="_foot.tpl"}
