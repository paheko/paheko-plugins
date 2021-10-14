-- Cache list of forms
CREATE TABLE IF NOT EXISTS plugin_helloasso_forms (
	id INTEGER PRIMARY KEY,
	org_name TEXT NOT NULL,
	name TEXT NOT NULL,
	type TEXT NOT NULL,
	status TEXT NOT NULL,

	org_slug TEXT NOT NULL,
	form_type TEXT NOT NULL,
	form_slug TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS plugin_helloasso_targets (
-- List of forms that should create users or subscriptions
	id INTEGER PRIMARY KEY,

	label TEXT NOT NULL,

	org_slug TEXT NOT NULL,
	form_type TEXT NOT NULL,
	form_slug TEXT NOT NULL,

	last_sync TEXT NULL,

	-- Targets in Garradin
	id_category INTEGER NOT NULL REFERENCES users_categories(id),
	id_fee INTEGER NULL REFERENCES services_fees(id),
	id_account INTEGER NULL REFERENCES acc_accounts(id),

	CHECK (id_account IS NULL OR (id_account IS NOT NULL AND id_fee IS NOT NULL))
);

-- Make sure we can't link to an invalid account if the linked fee changes its accounting chart
CREATE TRIGGER IF NOT EXISTS plugin_helloasso_targets_safe AFTER UPDATE OF id_year ON services_fees BEGIN
    UPDATE plugin_helloasso_targets SET id_account = NULL WHERE id_fee = OLD.id;
END;

CREATE TABLE IF NOT EXISTS plugin_helloasso_sync (
-- Contains the list of payments synced
	id INTEGER PRIMARY KEY,
	id_user INTEGER NULL REFERENCES membres (id),
	id_service_user INTEGER NULL REFERENCES services_users (id),

	order_id TEXT NOT NULL,
	payment_id TEXT NOT NULL,
	date TEXT NOT NULL,
	amount INTEGER NOT NULL,
	receipt_url TEXT
);

CREATE UNIQUE INDEX IF NOT EXISTS plugin_helloasso_sync_id ON plugin_helloasso_sync (payment_id);
CREATE INDEX IF NOT EXISTS plugin_helloasso_sync_date ON plugin_helloasso_sync (date);