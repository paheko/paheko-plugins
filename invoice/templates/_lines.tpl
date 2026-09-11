<table class="list">
	<thead>
		<tr>
			<th scope="col">Libellé</th>
			<td scope="col" class="money">Prix unitaire</td>
			<td scope="col" class="money">Quantité</td>
			<td scope="col" class="money">Total HT</td>
			<td scope="col" class="money">Taux TVA</td>
			<td scope="col" class="money">Total TTC</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
	{foreach from=$export.lines item="line"}
		<tr>
			<td>
				<strong>{$line.item_information.name}</strong>
				{if $line.item_information.seller_identifier}
					<small>Réf. {$line.item_information.seller_identifier}</small>
				{/if}
				{if $line.item_information.description}
					<br /><em>{$line.item_information.description|escape|nl2br}</em>
				{/if}
			</td>
		<td class="money">{$line.price_details.item_net_price|raw|money_int|money_currency_html:false}</td>
		<td class="money">{$line.invoiced_quantity|unit:$line.invoiced_quantity_code:false} <small>{$line.invoiced_quantity_code|get_unit_label}</small></td>
		<td class="money">{$line.net_amount|raw|money_int|money_currency_html:false}</td>
		<td class="money">{$line.vat_information.invoiced_item_vat_rate|format_vat_rate}</td>
		<td class="money">{$line.line_with_vat_net_amount|raw|money_int|money_currency_html:false}</td>
		<td class="actions">
			{if $invoice->isDraft() && $session->canAccess($session::SECTION_ACCOUNTING, $session::ACCESS_WRITE)}
				{button name="delete_line" type="submit" value=$line.identifier label="Supprimer" shape="delete"}
				{linkbutton shape="edit" label="Modifier" href="line.php?id=%d"|args:$line.identifier}
			{/if}
		</td>
	</tr>
{/foreach}
</tbody>
<tfoot>
	<tr>
		<th scope="row" colspan="5" class="total">Total HT</th>
		<td class="money">{$export.totals.total_without_vat|raw|money_int|money_currency_html:false}</td>
		<td></td>
	</tr>
	{foreach from=$export.vat_break_down item="vat"}
		<tr>
			<td scope="row" colspan="5" class="total">
				TVA à {$vat.vat_category_rate|format_vat_rate}
				{if $vat.vat_exemption_reason}
					<br />
					<small>TVA non applicable — {$vat.vat_exemption_reason}</small>
				{/if}
			</td>
			<td class="money">{$vat.vat_category_tax_amount|raw|money_int|money_currency_html:false}</td>
			<td></td>
		</tr>
	{/foreach}
	<tr>
		<th scope="row" colspan="5" class="total">Total TTC</th>
		<td class="money">{$export.totals.total_with_vat|raw|money_int|money_currency_html:false}</td>
		<td></td>
	</tr>
</tfoot>
</table>