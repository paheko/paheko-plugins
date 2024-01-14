{include file="_head.tpl" title="Outils comptables avancés"}

<div style="display: flex; gap: 3em">
	<div><img src="icon.svg" width="128" /></div>
	<div>
		<p>
			Ces outils sont utilisés par notre association, et sont mis à disposition au cas où ils seraient utiles à d'autres.<br />
			<strong>Aucun support ne sera apporté sur cette extension, désolé.</strong>
		</p>

		<dl class="large">
			<dt><a href="affectation.php">Affectation automatique de comptes</a></dt>
			<dd>Affecte automatiquement des comptes de débit/crédit à un fichier d'import simple Paheko, en fonction du libellé de l'écriture.</dd>
			<dt><a href="paypal.php">Conversion CSV PayPal</a></dt>
			<dd>Permet de créer un fichier d'import simple pour Paheko avec un export CSV de PayPal.</dd>
			<dd>Cet outil rajoute aussi automatiquement des écritures pour les commissions PayPal.</dd>
			{if $has_java}
				<dt><a href="credit_mutuel.php">Conversion PDF &rarr; CSV Crédit Mutuel Grand Est</a></dt>
				<dd>Permet de créer un fichier d'import simple pour Paheko avec un relevé de compte PDF du Crédit Mutuel Grand Est.</dd>
			{else}
				<dt>Conversion PDF &rarr; CSV Crédit Mutuel Grand Est</dt>
				<dd>Cet outil n'est pas disponible sur ce serveur.</dd>
			{/if}
		</dl>
	</div>
</div>
{include file="_foot.tpl"}
