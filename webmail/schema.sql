CREATE TABLE IF NOT EXISTS plugin_webmail_accounts (
	id INTEGER NOT NULL PRIMARY KEY,
	id_user INTEGER NOT NULL REFERENCES users (id) ON DELETE CASCADE,
	address TEXT NOT NULL,
	login TEXT NULL,
	id_vault_key INTEGER NULL REFERENCES vaults_keys (id) ON DELETE SET NULL,
	imap_server TEXT NOT NULL,
	imap_port INTEGER NOT NULL,
	imap_security TEXT NOT NULL,
	smtp_server TEXT NOT NULL,
	smtp_port INTEGER NOT NULL,
	smtp_security TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS plugin_webmail_folders (
	id INTEGER NOT NULL PRIMARY KEY,
	id_account INTEGER NOT NULL REFERENCES plugin_webmail_accounts (id) ON DELETE CASCADE,
	name TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS plugin_webmail_identities (
	id INTEGER NOT NULL PRIMARY KEY,
	id_account INTEGER NOT NULL REFERENCES plugin_webmail_accounts (id) ON DELETE CASCADE,
	name TEXT NOT NULL,
	address TEXT NOT NULL,
	signature TEXT NOT NULL,
	verified TEXT NULL CHECK (verified IS NULL OR datetime(verified) = verified)
);
