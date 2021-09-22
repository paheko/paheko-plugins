<?php

namespace Garradin\Plugin\Webstats;

use Garradin\DB;

class Stats
{
	const UA_BOT = 1;
	const UA_MOBILE = 2;
	const UA_DESKTOP = 3;

	const UA_BOTS_MATCH = '/Googlebot|Bingbot|Slurp|DuckDuckBot|Baiduspider|YandexBot|Exabot|facebookexternalhit|facebot|curl|ia_archiver|GoogleImageProxy|MJ12bot|MegaIndex|https?:\/\/|wget/i';
	const UA_MOBILE_MATCH = '/Mobile|iPhone|Android|Opera Mobi|Opera Mini|UCBrowser|SamsungBrowser/i';

	const IGNORE_URIS = '/apple-touch-icon|favicon\.ico|robots\.txt/';

	static protected $new_visitor = false;
	static protected $ua_type = null;

	static public function getUserAgentType(): int
	{
		$ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

		if (!$ua || preg_match(self::UA_BOTS_MATCH, $ua)) {
			return self::UA_BOT;
		}
		elseif (preg_match(self::UA_MOBILE_MATCH, $ua)) {
			return self::UA_MOBILE;
		}
		else {
			return self::UA_DESKTOP;
		}
	}

	static public function signalBefore(array $params, ?array $return): void
	{
		self::$ua_type = self::getUserAgentType();

		// Ignore bots
		if (self::$ua_type == self::UA_BOT) {
			return;
		}

		if (!headers_sent() && empty($_COOKIE['__visitor'])) {
			setcookie('__visitor', '1', time() + (3600 * 6), '/');
			self::$new_visitor = true;
		}
	}

	static public function signalAfter(array $params, ?array $return): void
	{
		// Ignore bots
		if (self::$ua_type == self::UA_BOT) {
			return;
		}

		if (preg_match(self::IGNORE_URIS, $params['uri'] ?? '')) {
			return;
		}

		$db = DB::getInstance();

		$sql = sprintf('BEGIN; INSERT INTO plugin_webstats_stats (year, month, day, mobile_visits) VALUES (%d, %d, %d, %d)
			ON CONFLICT (year, month, day) DO UPDATE SET hits = hits + 1, ',
			(int) date('Y'),
			(int) date('m'),
			(int) date('d'),
			self::$ua_type == self::UA_MOBILE ? 1 : 0
		);

		if (self::$new_visitor) {
			$sql .= 'visits = visits + 1, ';
		}

		if (self::$ua_type == self::UA_MOBILE) {
			$sql .= 'mobile_visits = mobile_visits + 1, ';
		}

		$sql = rtrim($sql, ', ');
		$sql .= ';';

		$uri = $params['uri'] ?? '';
		$uri = strtok($uri, '?');

		$sql .= sprintf('INSERT INTO plugin_webstats_hits (uri) VALUES (%s) ON CONFLICT (uri) DO UPDATE SET hits = hits + 1; END',
			$db->quote($uri));

		$db->exec($sql);
	}

	static public function getStats()
	{
		$db = DB::getInstance();
		return $db->get('SELECT
			printf(\'%04d-%02d-01\', year, month) AS date,
			SUM(visits) AS visits,
			SUM(mobile_visits) AS mobile_visits,
			SUM(hits) AS hits
			FROM plugin_webstats_stats
			GROUP BY year, month
			ORDER BY year DESC, month ASC;');
	}

	static public function getHits()
	{
		$db = DB::getInstance();
		return $db->get('SELECT
			uri,
			hits
			FROM plugin_webstats_hits
			ORDER BY hits DESC LIMIT 50;');
	}
}
