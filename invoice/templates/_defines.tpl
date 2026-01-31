{{#define function="display_status_label"}}
	{{if $status === 'draft'}}
		Brouillon
	{{elseif $type === 'quote'}}
		{{if $status === 'waiting'}}
			En attente
		{{elseif $status === 'ok'}}
			Accepté
		{{elseif $status == 'cancelled'}}
			Annulé
		{{/if}}
	{{else}}
		{{if $status === 'ok'}}
			Payée
		{{elseif $status === 'waiting'}}
			En souffrance
		{{elseif $status == 'cancelled'}}
			Annulée
		{{/if}}
	{{/if}}
{{/define}}
