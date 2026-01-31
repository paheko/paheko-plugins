CREATE TABLE IF NOT EXISTS plugin_invoice_clients (
	id INTEGER NOT NULL PRIMARY KEY,
	archived INTEGER NOT NULL DEFAULT 0,
	name TEXT NOT NULL,
	address TEXT NOT NULL,
	country TEXT NOT NULL,
	phone TEXT NULL,
	email TEXT NULL,
	notes TEXT NULL,
	business_number TEXT NULL,
	vat_number TEXT NULL
);

CREATE TABLE IF NOT EXISTS plugin_invoice_documents (
	id INTEGER NOT NULL PRIMARY KEY,
	id_client INTEGER NOT NULL REFERENCES plugin_invoice_clients (id),
	id_transaction INTEGER NULL REFERENCES acc_transactions (id) ON DELETE SET NULL,
	id_quote INTEGER NULL REFERENCES plugin_invoice_documents (id) ON DELETE SET NULL,
	number INTEGER NULL,
	type TEXT NOT NULL,
	label TEXT NOT NULL,
	date_created TEXT NOT NULL CHECK (date_created = date(date_created)),
	date_expiry TEXT NULL CHECK (date_expiry IS NULL OR date_expiry = date(date_expiry)),
	date_sent TEXT NULL CHECK (date_sent IS NULL OR date_sent = date(date_sent)),
	status TEXT NOT NULL,
	total INTEGER NOT NULL DEFAULT 0,
	header_text TEXT NULL,
	html TEXT NULL,
	client_ref TEXT NULL,
	details TEXT NULL
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
	description TEXT NULL,
	unit TEXT NULL,
	quantity REAL NOT NULL,
	price INTEGER NOT NULL
);
