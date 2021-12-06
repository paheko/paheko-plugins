-- Update stock from what we have for tabs items
UPDATE @PREFIX_products SET stock = -(SELECT SUM(ti.qty) FROM @PREFIX_tabs_items ti WHERE ti.product = @PREFIX_products.id)
	WHERE stock IS NOT NULL AND id IN (SELECT DISTINCT ti.product FROM @PREFIX_tabs_items ti WHERE ti.product IS NOT NULL);
