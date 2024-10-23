{include file="_head.tpl" title="Suppression lieu de vente"}

{include file="common/delete_form.tpl"
	legend="Supprimer ce lieu de vente ?"
	confirm="Cocher cette case pour supprimer le lieu de vente."
	warning="Êtes-vous sûr de vouloir supprimer le lieu de vente « %s » ?"|args:$location.name
	alert="Il ne pourra pas être supprimé si des sessions de caisse ont été ouvertes avec ce lieu de vente."
}

{include file="_foot.tpl"}