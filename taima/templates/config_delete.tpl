{include file="_head.tpl" title="Suivi du temps"}

{include file="common/delete_form.tpl"
	legend="Supprimer cette catégorie ?"
	warning="Êtes-vous sûr de vouloir supprimer la catégorie « %s » ?"|args:$task.label
	info="Les tâches liées à cette catégorie ne seront pas supprimées, mais se retrouveront sans aucune catégorie associée."}

{include file="_foot.tpl"}