CREATE TABLE IF NOT EXISTS plugin_chat_channels (
	id INTEGER NOT NULL PRIMARY KEY,
	name TEXT NULL,
	description TEXT NULL,
	access TEXT NOT NULL, -- public/private/pm
	archived INTEGER NOT NULL DEFAULT 0,
	delete_after INTEGER NULL,
	max_history INTEGER NULL
);

CREATE TABLE IF NOT EXISTS plugin_chat_users (
	id INTEGER NOT NULL PRIMARY KEY,
	id_user INTEGER NULL REFERENCES users (id) ON DELETE SET NULL,
	name TEXT NOT NULL,
	session_id TEXT NULL, -- Session token for anonymous users, or NULL for logged-in users
	last_disconnect INTEGER NULL, -- This is updated when user disconnects from SSE, to maintain online/offline status
	last_connect INTEGER NULL -- Last time a user joined a channel, used to maintain online/offline status
);

CREATE INDEX IF NOT EXISTS plugin_chat_users_idx ON plugin_chat_users (id_user, last_disconnect);

CREATE TABLE IF NOT EXISTS plugin_chat_users_channels (
	id_channel INTEGER NOT NULL REFERENCES plugin_chat_channels ON DELETE CASCADE,
	id_user INTEGER NOT NULL REFERENCES plugin_chat_users (id) ON DELETE CASCADE,
	last_seen_message_id INTEGER NULL, -- No foreign key: doesn't matter if ID doesn't exit anymore, it's just used to know if we have unread messages
	last_connect INTEGER NULL -- Last time the channel was joined, used to join back last opened channel
);

CREATE UNIQUE INDEX IF NOT EXISTS plugin_chat_users_channels_unique ON plugin_chat_users_channels (id_channel, id_user);

CREATE TABLE IF NOT EXISTS plugin_chat_channels_invites (
	id INTEGER NOT NULL PRIMARY KEY,
	id_user INTEGER NULL REFERENCES plugin_chat_users (id) ON DELETE CASCADE,
	id_channel INTEGER NOT NULL REFERENCES plugin_chat_channels ON DELETE CASCADE,
	email TEXT NOT NULL,
	hash TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS plugin_chat_messages (
	id INTEGER NOT NULL PRIMARY KEY,
	id_channel INTEGER NOT NULL REFERENCES plugin_chat_channels ON DELETE CASCADE,
	id_thread INTEGER NULL REFERENCES plugin_chat_messages(id) ON DELETE CASCADE,
	title TEXT NULL,
	added INTEGER NOT NULL,
	id_user INTEGER NULL REFERENCES plugin_chat_users (id) ON DELETE SET NULL,
	user_name TEXT NULL,
	type TEXT NULL, -- NULL if deleted
	id_file INTEGER NULL REFERENCES files(id) ON DELETE SET NULL, -- NULL if deleted
	content TEXT NULL,
	reactions TEXT NULL,
	last_updated INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS plugin_chat_messages_idx ON plugin_chat_messages (id_channel, last_updated);

CREATE VIRTUAL TABLE IF NOT EXISTS plugin_chat_messages_search USING fts4
-- Search inside messages content
(
	content="plugin_chat_messages",
	tokenize=unicode61, -- Available from SQLITE 3.7.13 (2012)
	title TEXT NULL,
	content TEXT NULL -- Text content
);

CREATE TRIGGER IF NOT EXISTS plugin_chat_messages_search_u
	AFTER UPDATE ON plugin_chat_messages
BEGIN
	DELETE FROM plugin_chat_messages_search WHERE docid = old.rowid;
END;

CREATE TRIGGER IF NOT EXISTS plugin_chat_messages_search_d
	AFTER DELETE ON plugin_chat_messages
BEGIN
	DELETE FROM plugin_chat_messages_search WHERE docid = old.rowid;
END;

-- Only insert if title or content is not null
CREATE TRIGGER IF NOT EXISTS plugin_chat_messages_search_au
	AFTER UPDATE ON plugin_chat_messages
	WHEN new.content IS NOT NULL OR new.title IS NOT NULL
BEGIN
	INSERT INTO plugin_chat_messages_search(docid, title, content) VALUES(new.rowid, new.title, new.content);
END;

CREATE TRIGGER IF NOT EXISTS plugin_chat_messages_search_ai
	AFTER INSERT ON plugin_chat_messages
	WHEN new.content IS NOT NULL OR new.title IS NOT NULL
BEGIN
	INSERT INTO plugin_chat_messages_search(docid, title, content) VALUES(new.rowid, new.title, new.content);
END;

INSERT OR IGNORE INTO plugin_chat_channels VALUES (1, 'interne', 'Discussions internes à l''association', 'private', 0, NULL, NULL);
