{include file="_head.tpl" title="Supprimer une catégorie" current="plugin_pim" }

{include file="common/delete_form.tpl"
	legend="Supprimer cette catégorie ?"
	confirm="Cocher cette case pour supprimer cette catégorie et tous ses événements"
	help="Les événements de cette catégorie seront aussi supprimés."
	warning="Êtes-vous sûr de vouloir supprimer la catégorie « %s » ?"|args:$cat.title}

{include file="_foot.tpl"}