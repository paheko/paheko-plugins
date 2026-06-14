CREATE TABLE IF NOT EXISTS plugin_invoice_clients (
	id INTEGER NOT NULL PRIMARY KEY,
	archived INTEGER NOT NULL DEFAULT 0,
	name TEXT NOT NULL,
	country TEXT NOT NULL,
	address TEXT NULL,
	post_code TEXT NULL,
	city TEXT NULL,
	phone TEXT NULL,
	email TEXT NULL,
	notes TEXT NULL,
	business_number TEXT NULL,
	vat_number TEXT NULL,
	created DATETIME NOT NULL CHECK (created = datetime(created)) DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS plugin_invoice_documents (
	id INTEGER NOT NULL PRIMARY KEY,
	id_client INTEGER NOT NULL REFERENCES plugin_invoice_clients (id),
	id_transaction INTEGER NULL REFERENCES acc_transactions (id) ON DELETE SET NULL,
	id_quote INTEGER NULL REFERENCES plugin_invoice_documents (id) ON DELETE SET NULL,
	number INTEGER NULL,
	type INTEGER NOT NULL,
	label TEXT NOT NULL,
	date_created TEXT NOT NULL CHECK (date_created = date(date_created)),
	date_expiry TEXT NULL CHECK (date_expiry IS NULL OR date_expiry = date(date_expiry)),
	date_sent TEXT NULL CHECK (date_sent IS NULL OR date_sent = date(date_sent)),
	status TEXT NOT NULL,
	total INTEGER NOT NULL DEFAULT 0,
	notes TEXT NULL,
	buyer_ref TEXT NULL, -- Buyer reference (Factur-X: code du service exécutant)
	contract_reference TEXT NULL, -- Factur-X : Numéro d'engagement
	content TEXT NULL, -- Content of generated invoice (JSON/EN16931 serialization), NULL if it's a draft
	submission_date DATETIME NULL CHECK (datetime(submission_date) = submission_date OR submission_date IS NULL), -- submittedAt
	submission_id TEXT NULL, -- flowId
	submission_provider TEXT NULL
);

CREATE TABLE IF NOT EXISTS plugin_invoice_payments (
	id INTEGER NOT NULL PRIMARY KEY,
	id_document INTEGER NOT NULL REFERENCES plugin_invoice_documents (id) ON DELETE CASCADE,
	id_transaction INTEGER NULL REFERENCES acc_transactions (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS plugin_invoice_lines (
	id INTEGER NOT NULL PRIMARY KEY,
	id_document INTEGER NOT NULL REFERENCES plugin_invoice_documents (id) ON DELETE CASCADE,
	number INTEGER NOT NULL,
	label TEXT NOT NULL,
	reference TEXT NULL,
	description TEXT NULL,
	unit TEXT NOT NULL,
	quantity REAL NOT NULL,
	price INTEGER NOT NULL,
	vat_code TEXT NOT NULL,
	vat_rate REAL NOT NULL
);
