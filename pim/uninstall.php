<?php

namespace Paheko;

DB::getInstance()->exec('
DROP TABLE IF EXISTS plugin_pim_changes;
DROP TABLE IF EXISTS plugin_pim_contacts;
DROP TABLE IF EXISTS plugin_pim_events;
DROP TABLE IF EXISTS plugin_pim_events_categories;
');
