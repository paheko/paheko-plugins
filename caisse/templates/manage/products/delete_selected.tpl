{include file="_head.tpl" title="Supprimer les produits sélectionnés"}

{include file="common/delete_form.tpl"
	legend="Supprimer les produits sélectionnés"
	confirm="Cocher cette case pour supprimer les produits et leur stock !"
	warning="Êtes-vous sûr de vouloir supprimer %d produits ?"|args:$count
	alert="Attention, cela supprimera également tout l'historique de stock lié au produits."
	info="Les notes en cours et clôturées ne seront pas modifiées, elles garderont une trace des produits au moment de leur ajout dans la note."}


{include file="_foot.tpl"}