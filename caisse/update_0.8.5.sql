ALTER TABLE @PREFIX_products ADD COLUMN id_fee INTEGER NULL REFERENCES services_fees (id) ON DELETE SET NULL;
ALTER TABLE @PREFIX_tabs_items ADD COLUMN id_fee INTEGER NULL REFERENCES services_fees (id) ON DELETE SET NULL;
ALTER TABLE @PREFIX_tabs_items ADD COLUMN id_subscription INTEGER NULL REFERENCES services_users (id) ON DELETE SET NULL;