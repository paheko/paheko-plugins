CREATE TABLE IF NOT EXISTS @PREFIX_sessions_balances (
	id INTEGER NOT NULL PRIMARY KEY,
	id_session INTEGER NOT NULL REFERENCES @PREFIX_sessions (id) ON DELETE CASCADE,
	id_method INTEGER NULL REFERENCES @PREFIX_methods (id) ON DELETE CASCADE,
	open_amount INTEGER NOT NULL,
	close_amount INTEGER NULL,
	error_amount INTEGER NULL
);

INSERT INTO @PREFIX_sessions_balances
	SELECT
		NULL,
		id,
		(SELECT id FROM @PREFIX_methods WHERE type = 1 ORDER BY is_default DESC LIMIT 1),
		open_amount,
		close_amount,
		error_amount
	FROM @PREFIX_sessions;

CREATE UNIQUE INDEX IF NOT EXISTS @PREFIX_sessions_balances_unique ON @PREFIX_sessions_balances (id_session, id_method);

ALTER TABLE @PREFIX_sessions RENAME TO @PREFIX_sessions_old;

CREATE TABLE IF NOT EXISTS @PREFIX_sessions (
	-- Cash register sessions
	id INTEGER NOT NULL PRIMARY KEY,
	id_location INTEGER NULL REFERENCES @PREFIX_locations (id) ON DELETE RESTRICT,
	opened TEXT NOT NULL DEFAULT (datetime('now','localtime')),
	closed TEXT NULL,
	open_user TEXT NULL,
	close_user TEXT NULL,
	result INTEGER NULL,
	nb_tabs INTEGER NULL
);

INSERT INTO @PREFIX_sessions
	SELECT
		s.id,
		s.id_location,
		s.opened,
		s.closed,
		s.open_user,
		s.close_user,
		NULL,
		NULL
	FROM @PREFIX_sessions_old s;

UPDATE @PREFIX_sessions SET result = (SELECT SUM(ti.total) FROM @PREFIX_tabs_items ti INNER JOIN @PREFIX_tabs t ON t.id = ti.tab WHERE t.session = @PREFIX_sessions.id);
UPDATE @PREFIX_sessions SET nb_tabs = (SELECT COUNT(*) FROM @PREFIX_tabs WHERE session = @PREFIX_sessions.id);

DROP TABLE @PREFIX_sessions_old;

ALTER TABLE @PREFIX_tabs_items ADD COLUMN id_method INTEGER NULL REFERENCES @PREFIX_methods (id) ON DELETE RESTRICT;

UPDATE @PREFIX_tabs_items SET id_method = (SELECT id FROM @PREFIX_methods WHERE enabled = 1 AND type = 2 AND account = @PREFIX_tabs_items.account) WHERE type = 1;

-- New indexes, this gives us a 30 ms gain on tab page
CREATE INDEX IF NOT EXISTS @PREFIX_tabs_items_type_tab ON @PREFIX_tabs_items(type, tab);

DROP TRIGGER IF EXISTS @PREFIX_tabs_account1;
ALTER TABLE @PREFIX_tabs_payments RENAME TO @PREFIX_tabs_payments_old;

CREATE TABLE IF NOT EXISTS @PREFIX_tabs_payments (
	-- Payments for a tab
	id INTEGER NOT NULL PRIMARY KEY,
	tab INTEGER NOT NULL REFERENCES @PREFIX_tabs (id) ON DELETE CASCADE,
	method INTEGER NOT NULL REFERENCES @PREFIX_methods (id) ON DELETE RESTRICT,
	date TEXT NOT NULL DEFAULT (datetime('now','localtime')),
	amount INTEGER NOT NULL, -- Can be negative for a refund
	reference TEXT NULL,
	account TEXT NULL,
	type INTEGER NOT NULL -- Copy of method type
);

INSERT INTO @PREFIX_tabs_payments SELECT tp.id, tp.tab, tp.method, tp.date, tp.amount, tp.reference, tp.account, m.type
	FROM @PREFIX_tabs_payments_old tp
	INNER JOIN @PREFIX_methods m ON m.id = tp.method;

DROP TABLE @PREFIX_tabs_payments_old;

CREATE INDEX IF NOT EXISTS @PREFIX_tabs_payments_tab ON @PREFIX_tabs_payments (tab);
CREATE INDEX IF NOT EXISTS @PREFIX_tabs_payments_tab_type ON @PREFIX_tabs_payments(type, tab);

CREATE TRIGGER IF NOT EXISTS @PREFIX_tabs_account1 AFTER UPDATE ON @PREFIX_methods
	WHEN (OLD.account != NEW.account OR OLD.type != NEW.type)
BEGIN
	UPDATE @PREFIX_tabs_payments SET account = NEW.account, type = NEW.type WHERE method = NEW.id;
END;
