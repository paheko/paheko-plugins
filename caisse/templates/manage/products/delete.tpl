{include file="_head.tpl" title="Gestion produit"}

{include file="common/delete_form.tpl"
	legend="Supprimer ce produit ?"
	confirm="Cocher cette case pour supprimer le produit et le stock associés !"
	warning="Êtes-vous sûr de vouloir supprimer le produit « %s » ?"|args:$product.name
	alert="Attention, cela supprimera également tout l'historique de stock lié au produit."
	info="Les notes en cours et clôturées ne seront pas modifiées, elles garderont une trace du produit au moment de son ajout dans la note."}


{include file="_foot.tpl"}