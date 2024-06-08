CREATE TABLE IF NOT EXISTS plugin_chat_channels (
	id INTEGER NOT NULL PRIMARY KEY,
	name TEXT NULL,
	description TEXT NULL,
	access TEXT NOT NULL, -- public/private/pm
	archived INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS plugin_chat_users (
	id INTEGER NOT NULL PRIMARY KEY,
	id_channel INTEGER NOT NULL REFERENCES plugin_chat_channels ON DELETE CASCADE,
	id_user INTEGER NULL REFERENCES users (id) ON DELETE SET NULL,
	name TEXT NULL,
	invitation TEXT NULL, -- Token for anonymous users, or 'invited' for logged-in users
	joined INTEGER NULL,
	last_disconnect INTEGER NULL, -- This is updated when user disconnects from SSE
	last_seen_message_id INTEGER NULL, -- No foreign key: doesn't matter if ID doesn't exit anymore, it's just used to know if we have unread messages
	CHECK (id_user IS NOT NULL OR name IS NOT NULL)
);

CREATE INDEX IF NOT EXISTS plugin_chat_users_idx ON plugin_chat_users (id_channel, id_user, last_disconnect);

CREATE TABLE IF NOT EXISTS plugin_chat_messages (
	id INTEGER NOT NULL PRIMARY KEY,
	id_channel INTEGER NOT NULL REFERENCES plugin_chat_channels ON DELETE CASCADE,
	id_thread INTEGER NULL REFERENCES plugin_chat_messages(id) ON DELETE CASCADE,
	added INTEGER NOT NULL,
	id_user INTEGER NULL REFERENCES plugin_chat_users (id) ON DELETE SET NULL,
	user_name TEXT NULL,
	type TEXT NULL, -- NULL if deleted
	id_file INTEGER NULL REFERENCES files(id) ON DELETE SET NULL, -- NULL if deleted
	content TEXT NULL,
	reactions TEXT NULL,
	last_updated INTEGER NOT NULL,
	CHECK (content IS NOT NULL OR id_file IS NOT NULL),
	CHECK (id_user IS NOT NULL OR user_name IS NOT NULL)
);

CREATE INDEX IF NOT EXISTS plugin_chat_messages_idx ON plugin_chat_messages (id_channel, last_updated);

CREATE VIRTUAL TABLE IF NOT EXISTS plugin_chat_messages_search USING fts4
-- Search inside messages content
(
	tokenize=unicode61, -- Available from SQLITE 3.7.13 (2012)
	content TEXT NULL -- Text content
);

INSERT OR IGNORE INTO plugin_chat_channels VALUES (1, 'Général', NULL, 'private', 0);
