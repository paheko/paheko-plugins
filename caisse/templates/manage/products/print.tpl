<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<meta charset="utf-8" />
	<title>Produits</title>
	<style type="text/css">
	{literal}
	@page {
		size: A4;
		margin: 1cm;
		margin-top: 1.5cm;
		@bottom {
			content: "Page " counter(page) " / " counter(pages);
			font-size: 8pt;
			margin-bottom: 10mm;
			text-align: center;
		}

	}
	* { margin: 0; padding: 0; }
	body {
		font-family: Arial, Helvetica, sans-serif;
		color: #000;
		font-size: 10pt;
		columns: 3;
	}
	h2 {
		font-size: 1.2rem;
	}

	table {
		margin: 1rem auto;
		border-collapse: collapse;
		width: 100%;
	}
	table tr {
		border: 1px solid #000;
	}
	table tr:nth-child(even) {
		background: #eee;
	}

	table th, table td {
		padding: .2rem .3rem;
		text-align: left;
	}
	table td {
		border-left: 1px dotted #999;
		text-align: right;
		font-variant-numeric: tabular-nums;
		font-feature-settings: "tnum";
	}
	{/literal}
	</style>
</head>

<body>

<section class="products">
	{foreach from=$products_categories key="category" item="products"}
		<section>
			<h2 class="ruler">{$category}</h2>

			<table>
			{foreach from=$products item="product"}
				<tr>
					<th>{$product.name}</th>
					<td>{$product.price|escape|money_currency}</td>
				</tr>
			{/foreach}
			</table>
		</section>
	{/foreach}
</section>

</body>
</html>