{{#load assign="doc" key=$_GET.doc}}
{{else}}
	{{:error message="Cet document n'existe pas"}}
{{/load}}

{{if $doc.status !== 'draft'}}
	{{:error message="Seuls les documents en brouillon peuvent être supprimés"}}
{{/if}}

{{#form on="delete"}}
	{{:delete type="line" document=$doc.key}}
	{{:delete type=$doc.type key=$doc.key}}
	{{:redirect parent="./"}}
{{/form}}

{{if $doc.type === 'quote'}}
	{{:admin_header title="Supprimer le devis"}}

	{{:delete_form
		legend="Supprimer un devis"
		warning="Supprimer le devis ?"
	}}
{{else}}
	{{:admin_header title="Supprimer la facture"}}

	{{:delete_form
		legend="Supprimer une facture"
		warning="Supprimer la facture ?"
	}}
{{/if}}

{{:admin_footer}}