{include file="_head.tpl" title="Gestion catégorie"}

{include file="common/delete_form.tpl"
	legend="Supprimer cette catégorie ?"
	confirm="Cocher cette case pour supprimer la catégorie et tous les produits et le stock associés !"
	warning="Êtes-vous sûr de vouloir supprimer la catégorie « %s » ?"|args:$cat.name
	alert="Attention, cela supprimera également tous les produits de cette catégorie et leur historique de stock."
	info="Les notes en cours et clôturées ne seront pas modifiées, elles garderont une trace du produit au moment de son ajout dans la note."}


{include file="_foot.tpl"}