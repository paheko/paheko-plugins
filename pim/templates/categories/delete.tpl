{include file="_head.tpl" title="Supprimer une catégorie" current="plugin_pim" }

{include file="common/delete_form.tpl"
	legend="Supprimer cette catégorie ?"
	confirm="Cocher cette case pour confirmer la suppression"
	help="Les événements de cette catégorie ne seront pas supprimés, mais déplacés dans la catégorie par défaut."
	warning="Êtes-vous sûr de vouloir supprimer la catégorie « %s » ?"|args:$cat.title}

{include file="_foot.tpl"}