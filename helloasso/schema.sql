CREATE TABLE IF NOT EXISTS plugin_helloasso_forms (
	id INTEGER PRIMARY KEY,

	org_name TEXT NOT NULL,
	org_slug TEXT NOT NULL,

	name TEXT NOT NULL,
	slug TEXT NOT NULL,
	type TEXT NOT NULL,
	state TEXT NOT NULL,

	raw_data TEXT NOT NULL,

	id_year INTEGER NULL REFERENCES acc_years(id) ON DELETE SET NULL,
	payment_account_code TEXT NULL
);

CREATE UNIQUE INDEX IF NOT EXISTS plugin_helloasso_forms_key ON plugin_helloasso_forms(org_slug, slug);

CREATE TABLE IF NOT EXISTS plugin_helloasso_forms_tiers (
-- Tiers: elements of a form
	id INTEGER PRIMARY KEY,
	id_form INTEGER NOT NULL REFERENCES plugin_helloasso_forms(id) ON DELETE CASCADE,
	label TEXT NULL,
	amount INTEGER NULL,
	type TEXT NOT NULL,

	custom_fields TEXT NULL,

	-- Set to an ID to create a subscription in this fee
	-- If the fee is linked to accounting, a transaction will be created
	id_fee INTEGER NULL REFERENCES services_fees(id) ON DELETE SET NULL,

	-- Which account should be used to create the transaction line for this option
	account_code TEXT NULL,

	-- JSON list of fields for mapping user information
	fields_map TEXT NULL,

	create_user INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS plugin_helloasso_forms_tiers_options (
	id INTEGER PRIMARY KEY,
	id_form INTEGER NOT NULL REFERENCES plugin_helloasso_forms(id) ON DELETE CASCADE,
	label TEXT NULL,
	amount INTEGER NULL,

	-- Which account should be used to create the transaction line for this option
	account_code TEXT NULL
);

CREATE TABLE IF NOT EXISTS plugin_helloasso_forms_tiers_options_links (
-- An option can be linked to more than one tier
	id_tier INTEGER NOT NULL REFERENCES plugin_helloasso_forms_tiers(id) ON DELETE CASCADE,
	id_tier_option INTEGER NOT NULL REFERENCES plugin_helloasso_forms_tiers_options(id) ON DELETE CASCADE,
	PRIMARY KEY(id_tier, id_tier_option)
);

CREATE TABLE IF NOT EXISTS plugin_helloasso_orders (
	id INTEGER PRIMARY KEY NOT NULL,
	id_form INTEGER NOT NULL REFERENCES plugin_helloasso_forms(id) ON DELETE CASCADE,
	id_user INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
	id_transaction INTEGER NULL REFERENCES acc_transactions(id) ON DELETE SET NULL,
	date TEXT NOT NULL,
	person TEXT NOT NULL,
	amount INTEGER NOT NULL,
	status TEXT NOT NULL,
	raw_data TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS plugin_helloasso_items (
	id INTEGER PRIMARY KEY NOT NULL,
	id_form INTEGER NOT NULL REFERENCES plugin_helloasso_forms(id) ON DELETE CASCADE,
	id_order INTEGER NOT NULL REFERENCES plugin_helloasso_orders(id) ON DELETE CASCADE,
	id_tier INTEGER NULL REFERENCES plugin_helloasso_forms_tiers(id) ON DELETE SET NULL,
	id_user INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
	id_subscription INTEGER NULL REFERENCES services_users(id) ON DELETE SET NULL,

	type TEXT NOT NULL,
	state TEXT NOT NULL,
	label TEXT NOT NULL,
	amount INTEGER NOT NULL,
	raw_data TEXT NOT NULL,
	custom_fields TEXT NULL
);

CREATE TABLE IF NOT EXISTS plugin_helloasso_payments (
	id INTEGER PRIMARY KEY NOT NULL,
	id_form INTEGER NOT NULL REFERENCES plugin_helloasso_forms(id) ON DELETE CASCADE,
	id_order INTEGER NOT NULL REFERENCES plugin_helloasso_orders(id) ON DELETE CASCADE,
	id_user INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
	id_transaction INTEGER NULL REFERENCES acc_transactions(id) ON DELETE SET NULL,
	amount INTEGER NOT NULL,
	state TEXT NOT NULL,
	transfer_date TEXT NULL,
	date TEXT NOT NULL,
	receipt_url TEXT NULL,
	raw_data TEXT NOT NULL
);
