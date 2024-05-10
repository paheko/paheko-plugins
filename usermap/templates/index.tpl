{include file="_head.tpl" title=$plugin.label}

<nav class="tabs">
	<ul>
		<li class="current"><a href="./">Statistiques</a></li>
		<li><a href="map.php">Répartition sur la carte</a></li>
	</ul>
</nav>

{form_errors}

{if $_GET.msg === 'NOTHING'}
	<p class="error block">Aucune adresse n'a pu être localisée.</p>
{elseif $_GET.msg !== null}
	<p class="confirm block">{$_GET.msg} adresses ont été localisées.</p>
{/if}

{if $missing_users_count}
<form method="post" action="">
	<h2 class="ruler">Rechercher la localisation des membres</h2>
	<p class="help">{$missing_users_count} membres ne sont pas localisés. Pour les localiser, il est nécessaire d'envoyer leur adresse à un service de géolocalisation.</p>
	<div class="alert block">
		<h3>Attention&nbsp;: cliquer sur le bouton ci-dessous envoie l'adresse postale de tous vos membres au service <a href="https://api.gouv.fr/les-api/base-adresse-nationale" target="_blank">Base adresse nationale</a> du gouvernement français.</h3>
		<p><em>Aucune autre donnée personnelle n'est envoyée.</em></p>
		<p>Le service « Base adresse nationale » enregistre ensuite les adresses qui ont été envoyées, ainsi que l'adresse IP du serveur ayant réalisé la requête.</p>
	</div>
	<p class="help">Cette action peut prendre plus d'une minute.</p>
	<p class="submit">
		{csrf_field key=$csrf_key}
		{button shape="right" type="submit" class="main" name="sync" label="Recherche la localisation"}
	</p>
</form>
{/if}

{if $count && $stats}
<form method="get" action="">
	<h2 class="ruler">Statistiques sur la distance des membres</h2>
	<p class="help">Il y a {$count} membres localisés.</p>
	<fieldset>
		<legend>Calculer la distance des membres par rapport à une adresse</legend>
		<dl>
			{input type="text" name="address" default=$address label="Adresse" required=true}
		</dl>
		<p>
			{button type="submit" label="Calculer" shape="right"}
		</p>
	</fieldset>
	<table class="list auto">
		<thead>
			<tr>
				<th>Distance</th>
				<td>Nombre de membres</td>
				<td>Part</td>
			</tr>
		</thead>
		<tbody>
		{foreach from=$stats item="row"}
			<tr>
				<th>{$row.label}</th>
				<td>{$row.count}</td>
				<td>{$row.percent}%</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
</form>
{/if}

{include file="_foot.tpl"}