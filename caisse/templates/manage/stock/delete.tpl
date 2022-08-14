{include file="_head.tpl" title="Gestion stock" current="plugin_%s"|args:$plugin.id}

{include file="common/delete_form.tpl"
	legend="Supprimer cet événement de stock ?"
	confirm="Cocher cette case pour supprimer l'événement !"
	warning="Êtes-vous sûr de vouloir supprimer l'événement « %s » ?"|args:$event.label
	alert="Attention, cela modifiera le stock actuel des produits (l'événement n'aura pas eu lieu)."
	}


{include file="_foot.tpl"}