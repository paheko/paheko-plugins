CREATE TABLE IF NOT EXISTS plugin_discuss_forums (
	id INTEGER PRIMARY KEY NOT NULL,
	uri TEXT NOT NULL,
	title TEXT NOT NULL,
	language TEXT NOT NULL,
	description TEXT NULL,
	subscribe_permission TEXT NOT NULL,
	post_permission TEXT NOT NULL,
	archives_permission TEXT NOT NULL,
	attachment_permission TEXT NOT NULL,
	disable_archives INTEGER NOT NULL,
	template_footer TEXT NULL,
	template_welcome TEXT NULL,
	template_goodbye TEXT NULL,
	pgp_secret_key TEXT NULL,
	pgp_public_key TEXT NULL,
	verify_messages INTEGER NOT NULL,
	encrypt_messages INTEGER NOT NULL,
	delete_forbidden_attachments INTEGER NOT NULL,
	resize_images INTEGER NOT NULL,
	max_attachment_size INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS plugin_discuss_users (
	id INTEGER PRIMARY KEY NOT NULL,
	id_user INTEGER NULL REFERENCES users (id) ON DELETE CASCADE,
	id_forum INTEGER NOT NULL REFERENCES plugin_discuss_forums (id) ON DELETE CASCADE,
	email TEXT NULL,
	name TEXT NULL,
	status INTEGER NOT NULL DEFAULT 0,
	subscription INTEGER NOT NULL DEFAULT 0,
	stats_posts INTEGER NOT NULL DEFAULT 0,
	stats_bounced INTEGER NOT NULL DEFAULT 0,
	created TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP CHECK (created = datetime(created)),
	last_post TEXT NULL CHECK (last_post IS NULL OR last_post = datetime(last_post)),
	has_avatar INTEGER NOT NULL DEFAULT 0,
	pgp_key TEXT NULL,
	CHECK (id_user IS NOT NULL OR email IS NOT NULL)
);

CREATE UNIQUE INDEX IF NOT EXISTS plugin_discuss_users_id ON plugin_discuss_users (id_forum, id_user, email);

CREATE TABLE IF NOT EXISTS plugin_discuss_threads (
	id INTEGER PRIMARY KEY NOT NULL,
	id_forum INTEGER NOT NULL REFERENCES plugin_discuss_forums (id) ON DELETE CASCADE,
	uri TEXT NOT NULL,
	subject TEXT NOT NULL,
	last_update TEXT NULL,
	status INTEGER NOT NULL DEFAULT 0,
	replies_count INTEGER NOT NULL DEFAULT 0
);

CREATE UNIQUE INDEX IF NOT EXISTS messages_uri ON plugin_discuss_threads (id_forum, uri);
CREATE INDEX IF NOT EXISTS messages_list ON plugin_discuss_threads (id_forum, last_update);

CREATE TABLE IF NOT EXISTS plugin_discuss_messages (
	id INTEGER PRIMARY KEY NOT NULL,
	id_thread INTEGER NOT NULL REFERENCES plugin_discuss_threads (id) ON DELETE CASCADE,
	id_parent INTEGER NULL REFERENCES plugin_discuss_messages (id) ON DELETE SET NULL,
	level INTEGER NOT NULL DEFAULT 0,
	message_id TEXT NOT NULL,
	in_reply_to TEXT NULL,
	date TEXT NOT NULL CHECK (date = datetime(date)),
	id_user INTEGER NOT NULL REFERENCES id_forum INTEGER NOT NULL REFERENCES plugin_discuss_forums (id),users(id) ON DELETE SET NULL,
	from_name TEXT NULL,
	from_email TEXT NULL,
	content TEXT NOT NULL,
	has_attachments INTEGER NOT NULL DEFAULT 0,
	deleted_attachments TEXT NULL,
	status INTEGER NOT NULL DEFAULT 0
);

CREATE INDEX IF NOT EXISTS messages_parent ON messages (id_parent);
CREATE INDEX IF NOT EXISTS messages_thread ON messages (id_thread);
CREATE INDEX IF NOT EXISTS messages_user_id ON messages (id_user);
CREATE INDEX IF NOT EXISTS messages_email ON messages (from_email);

CREATE TRIGGER IF NOT EXISTS plugin_discuss_messages_add AFTER INSERT ON plugin_discuss_messages BEGIN
	UPDATE plugin_discuss_threads SET replies = replies + 1 WHERE id = NEW.thread_id;
END;

CREATE TRIGGER IF NOT EXISTS plugin_discuss_messages_delete AFTER DELETE ON plugin_discuss_messages BEGIN
	UPDATE plugin_discuss_threads SET replies = replies - 1 WHERE id = OLD.thread_id;
END;

CREATE VIRTUAL TABLE IF NOT EXISTS plugin_discuss_search USING fts4
(
    tokenize=unicode61, -- Available from SQLITE 3.7.13 (2012)
    id_message INTEGER NULL,
    id_thread INTEGER NULL,
    subject TEXT NULL,
    content TEXT NULL,
    notindexed=id_message,
    notindexed=id_thread
);

CREATE TRIGGER IF NOT EXISTS plugin_discuss_search_delete_message AFTER DELETE ON plugin_discuss_messages BEGIN
	DELETE FROM plugin_discuss_search WHERE message_id = OLD.rowid;
END;

CREATE TRIGGER IF NOT EXISTS plugin_discuss_search_delete_thread AFTER DELETE ON plugin_discuss_threads BEGIN
	DELETE FROM plugin_discuss_search WHERE thread_id = OLD.rowid;
END;

CREATE TRIGGER IF NOT EXISTS plugin_discuss_search_update_thread AFTER UPDATE OF subject ON plugin_discuss_threads BEGIN
	UPDATE plugin_discuss_search SET subject = NEW.subject WHERE thread_id = NEW.rowid;
END;

CREATE TRIGGER IF NOT EXISTS plugin_discuss_search_update_message AFTER UPDATE OF content ON plugin_discuss_messages BEGIN
	UPDATE plugin_discuss_search SET content = NEW.content WHERE message_id = NEW.rowid;
END;

CREATE TRIGGER IF NOT EXISTS plugin_discuss_search_add_message AFTER INSERT ON plugin_discuss_messages BEGIN
	INSERT INTO plugin_discuss_search VALUES NEW.id, NEW.thread_id, NULL, NEW.content;
END;

CREATE TRIGGER IF NOT EXISTS plugin_discuss_search_add_thread AFTER INSERT ON plugin_discuss_threads BEGIN
	INSERT INTO plugin_discuss_search VALUES NULL, NEW.id, NEW.subject, NULL;
END;
