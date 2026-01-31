{include file="_head.tpl" title="Chercher un vélo"}

{include file="./_nav.tpl" current="recherche"}

<ul class="sub_actions">
	<li><a href="sql.php">Recherche SQL</a></li>
</ul>

<form method="get" action="{$self_url}">
	<fieldset>
		<legend>Rechercher un vélo</legend>
		<dl>
			<dt><label for="f_field">Dont le champ...</label></dt>
			<dd>
				<select name="f" id="f_field">
				{foreach from=$fields key="field" item="name"}
					<option value="{$field}"{if $field == $current_field} selected="selected"{/if}>{$name}</option>
				{/foreach}
				</select>
			</dd>
			<dt><label for="f_query">Contient ou correspond à...</label></dt>
			<dd>
				<input type="text" name="q" value="{$query}" id="f_query" />
			</dd>
		</dl>
		<p class="submit">
			<input type="submit" value="Chercher" />
		</p>
	</fieldset>
</form>

{if empty($liste)}
	<p class="block alert">Aucun vélo trouvé.</p>
{else}
	<h2>{$liste|count} vélos trouvés</h2>
	<table class="list">
		<thead>
			<tr>
				<th class="num">Num.</th>
				<td class="num">Stock</td>
				<td class="cur">{$fields[$current_field]}</td>
				<td>Type</td>
				<td>Roues</td>
				<td>Genre</td>
				<td>Prix</td>
				<td>Entrée</td>
				<td>Sortie</td>
			</tr>
		</thead>
		<tbody>
		{foreach from=$liste item="velo"}
			<tr>
				<th class="num"><a href="fiche.php?id={$velo.id}">{$velo.id}</a></th>
				<td class="num">{if is_null($velo.date_sortie)}<a href="fiche.php?id={$velo.id}">{$velo.etiquette}</a>{else}[{$velo.raison_sortie}]{/if}</td>
				<td>{$velo->$current_field}</td>
				<td>{$velo.type}</td>
				<td>{$velo.roues}</td>
				<td>{$velo.genre}</td>
				<td>{if empty($velo.prix)}--{elseif $velo.prix < 0}à&nbsp;démonter{else}{$velo.prix} €{/if}</td>
				<td>{$velo.date_entree|date_short}</td>
				<td>{if !is_null($velo.date_sortie)}{$velo.date_sortie|date_short}{/if}</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
{/if}

{include file="_foot.tpl"}