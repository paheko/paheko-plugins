{include file="_head.tpl" title="Suppression moyen de paiement" current="plugin_%s"|args:$plugin.id}

{include file="common/delete_form.tpl"
	legend="Supprimer ce moyen de paiement ?"
	confirm="Cocher cette case pour supprimer le moyen de paiement."
	warning="Êtes-vous sûr de vouloir supprimer le moyen de paiement « %s » ?"|args:$method.name
	alert="Il ne pourra pas être supprimé si des paiements ont été réalisés avec ce moyen dans des notes de caisse."
}

{include file="_foot.tpl"}