CREATE TABLE IF NOT EXISTS plugin_invoice_clients (
	id INTEGER NOT NULL PRIMARY KEY,
	archived INTEGER NOT NULL DEFAULT 0,
	name TEXT NOT NULL,
	country TEXT NOT NULL,
	address TEXT NOT NULL,
	post_code TEXT NOT NULL,
	city TEXT NOT NULL,
	email TEXT NOT NULL,
	phone TEXT NULL,
	notes TEXT NULL,
	business_number TEXT NULL,
	vat_number TEXT NULL,
	created DATETIME NOT NULL CHECK (created = datetime(created)) DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS plugin_invoice_invoices (
	id INTEGER NOT NULL PRIMARY KEY,
	id_client INTEGER NOT NULL REFERENCES plugin_invoice_clients (id),
	id_transaction INTEGER NULL REFERENCES acc_transactions (id) ON DELETE SET NULL,
	id_invoice INTEGER NULL REFERENCES plugin_invoice_invoices (id) ON DELETE SET NULL,
	type INTEGER NOT NULL,
	year INTEGER NULL,
	number INTEGER NULL,
	label TEXT NOT NULL,
	date_created TEXT NOT NULL CHECK (date_created = date(date_created)),
	date_expiry TEXT NOT NULL CHECK (date_expiry = date(date_expiry)),
	date_sent TEXT NULL CHECK (date_sent IS NULL OR date_sent = date(date_sent)), -- submittedAt in AFNOR
	status TEXT NOT NULL,
	total INTEGER NOT NULL DEFAULT 0,
	notes TEXT NULL,
	buyer_ref TEXT NULL, -- Buyer reference (Factur-X: code du service exécutant)
	contract_reference TEXT NULL, -- Factur-X : Numéro d'engagement
	operation_type TEXT NULL,
	content TEXT NULL, -- Content of generated invoice (JSON/EN16931 serialization), NULL if it's a draft
	provider_id TEXT NULL, -- ID returned by provider for this invoice (flowId in AFNOR)
	provider_name TEXT NULL -- Name of provider used for submission
);

CREATE UNIQUE INDEX plugin_invoice_invoices_number ON plugin_invoice_invoices (type, year, number);

CREATE TABLE IF NOT EXISTS plugin_invoice_payments (
	id INTEGER NOT NULL PRIMARY KEY,
	id_invoice INTEGER NOT NULL REFERENCES plugin_invoice_invoices (id) ON DELETE CASCADE,
	id_transaction INTEGER NULL REFERENCES acc_transactions (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS plugin_invoice_lines (
	id INTEGER NOT NULL PRIMARY KEY,
	id_invoice INTEGER NOT NULL REFERENCES plugin_invoice_invoices (id) ON DELETE CASCADE,
	number INTEGER NOT NULL,
	label TEXT NOT NULL,
	reference TEXT NULL,
	description TEXT NULL,
	unit TEXT NOT NULL,
	quantity TEXT NOT NULL,
	price TEXT NOT NULL,
	vat_rate TEXT NOT NULL,
	vat_code TEXT NOT NULL,
	vat_exemption_code TEXT NULL
);
