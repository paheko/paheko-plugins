<?php

namespace Paheko\Plugin\Usermap;

use Paheko\Config;
use Paheko\DB;
use Paheko\Utils;
use Paheko\UserException;
use Paheko\Users\DynamicFields;
use KD2\HTTP;

use const Paheko\{STATIC_CACHE_ROOT};

class Usermap
{
	const CSV_API_URL = 'https://api-adresse.data.gouv.fr/search/csv/';
	const SEARCH_API_URL = 'https://api-adresse.data.gouv.fr/search/?q=';

	protected array $fields = ['adresse', 'code_postal', 'ville', 'pays'];

	public function __construct()
	{
		if (!function_exists('curl_exec') || !class_exists('CurlFile')) {
			throw new \LogicException('Cette extension nécessite php-curl');
		}

		$df = DynamicFields::getInstance();

		foreach ($this->fields as $name) {
			if (!$df->get($name)) {
				throw new UserException(sprintf('Il manque le champ "%s" dans les fiches de membre, cette extension ne peut donc pas fonctionner.', $name));
			}
		}

		$config = Config::getInstance();

		if ($config->country !== 'FR') {
			throw new UserException('Cette extension ne fonctionne actuellement qu\'avec les adresses en france');
		}

		$db = DB::getInstance();
		$db->createFunction('DISTANCE_TO', [$this, 'haversineDistanceTo'], 4);
	}

	public function count(): int
	{
		return DB::getInstance()->count('plugin_usermap_locations');
	}

	public function haversineDistanceTo($lat1, $lon1, $lat2, $lon2)
	{
		if (is_null($lat1) || is_null($lon1) || is_null($lat2) || is_null($lon2))
			return null;

		$lat1 = (double) $lat1;
		$lon1 = (double) $lon1;
		$lat2 = (double) $lat2;
		$lon2 = (double) $lon2;

		// convert lat1 and lat2 into radians now, to avoid doing it twice below
		$lat1rad = deg2rad($lat1);
		$lat2rad = deg2rad($lat2);

		// apply the spherical law of cosines to our latitudes and longitudes, and set the result appropriately
		// 6378.1 is the approximate radius of the earth in kilometres
		return (acos(sin($lat1rad) * sin($lat2rad) + cos($lat1rad) * cos($lat2rad) * cos(deg2rad($lon2) - deg2rad($lon1))) * 6378.1);
	}

	public function normalizeAddress(?string $address): ?string
	{
		$address = str_replace("\n", ' ', (string) $address);
		$address = trim($address);
		return $address ?: null;
	}

	public function getDistanceStatsTo(string $address): ?array
	{
		$ll = $this->getLatLon($address);

		if (!$ll) {
			return null;
		}

		$db = DB::getInstance();

		$total = $db->count('plugin_usermap_locations');
		$distances = [
			'%s <= 1' => 'entre 0 et 1 km',
			'%s <= 2 AND %1$s > 1' => 'entre 1 et 2 km',
			'%s <= 5 AND %1$s > 2' => 'entre 2 et 5 km',
			'%s <= 10 AND %1$s > 5' => 'entre 5 et 10 km',
			'%s <= 25 AND %1$s > 10' => 'entre 10 et 25 km',
			'%s <= 50 AND %1$s > 25' => 'entre 25 et 50 km',
			'%s <= 100 AND %1$s > 50' => 'entre 50 et 100 km',
			'%s <= 250 AND %1$s > 100' => 'entre 100 et 250 km',
			'%s <= 500 AND %1$s > 250' => 'entre 250 et 500 km',
			'%s <= 1000 AND %1$s > 500' => 'entre 500 et 1000 km',
			'%s > 1000' => 'plus de 1000 km',
		];

		$sql = sprintf('CREATE TEMP TABLE plugin_usermap_locations_distances (distance);
			INSERT INTO plugin_usermap_locations_distances SELECT DISTANCE_TO(%s, %s, lat, lon) FROM plugin_usermap_locations WHERE lat IS NOT NULL;',
			$ll['lat'], $ll['lon']);
		$db->exec($sql);

		$sql = 'SELECT COUNT(*) FROM plugin_usermap_locations_distances WHERE %s;';
		$out = [];

		foreach ($distances as $where => $label) {
			$count = $db->firstColumn(sprintf($sql, sprintf($where, 'distance')));
			$percent = $count ? round(($count / $total)*100) : 0;
			$out[] = compact('label', 'count', 'percent');
		}

		return $out;
	}

	public function listCoordinates(): array
	{
		return DB::getInstance()->get('SELECT lat, lon FROM plugin_usermap_locations WHERE lat IS NOT NULL;');
	}

	public function getLatLon(string $address): ?array
	{
		$r = (new HTTP)->GET(self::SEARCH_API_URL . rawurlencode($address));
		$r = json_decode($r->body);
		$r = $r->features[0]->geometry->coordinates ?? null;

		if (!$r || !isset($r[1], $r[0])) {
			return null;
		}

		return ['lat' => $r[1], 'lon' => $r[0]];
	}

	public function getUsersLocations(): array
	{
		return DB::getInstance()->get('SELECT lat, lon FROM plugin_usermap_locations;');
	}

	public function getMissingUsersSQL(string $select = null, int $limit = null): string
	{
		$db = DB::getInstance();
		$fields = array_map([$db, 'quoteIdentifier'], $this->fields);
		$fields = array_map(fn($a) => 'u.' . $a, $this->fields);
		$where = implode(' AND ', array_map(fn($a) => $a . ' IS NOT NULL', $fields));
		$full_address = implode(' || \' \' || ', $fields);
		$full_address = 'TRIM(' . $full_address . ')';

		if (null === $select) {
			$select = 'md5(' . $full_address . ') AS address_hash, u.id, ' . implode(', ', $fields);
		}

		$sql = sprintf('SELECT %s
			FROM users u
			LEFT JOIN plugin_usermap_locations pul ON pul.id_user = u.id AND md5(%s) = pul.address_hash
			WHERE pul.id_user IS NULL AND u.pays = \'FR\' AND %s
			ORDER BY RANDOM() %s;',
			$select,
			$full_address,
			$where,
			empty($limit) ? '' : ' LIMIT ' . $limit
		);

		return $sql;
	}

	public function countMissingUsers(): int
	{
		$db = DB::getInstance();
		return $db->firstColumn($this->getMissingUsersSQL('COUNT(*)', 1));
	}

	public function syncUserLocations(): ?int
	{
		$tmp = tempnam(STATIC_CACHE_ROOT, 'usermap-');
		$destination = tempnam(STATIC_CACHE_ROOT, 'usermap2-');

		try {

			$db = DB::getInstance();

			$fields = $this->fields;
			unset($fields[array_search('pays', $fields, true)]);
			$columns = $fields;

			$fields = array_merge(['address_hash'], $fields);
			$users = [];

			$sql = $this->getMissingUsersSQL();
			$i = 0;
			// Use some random bytes so that the user ID cannot be found by remote API service
			$random = random_bytes(10);

			$fp = fopen($tmp, 'w');
			fputcsv($fp, $fields);

			foreach ($db->iterate($sql) as $row) {
				$foreign_hash = md5($random . $row->id . $row->address_hash);
				$users[$foreign_hash] = ['id' => $row->id, 'hash' => $row->address_hash];
				$row->address_hash = $foreign_hash;

				unset($row->id, $row->pays);
				$row = (array)$row;
				$row = array_map(fn($a) => str_replace(["\r", "\n"], '', $a), $row);
				$i++;
				fputcsv($fp, $row);
			}

			fclose($fp);

			// Nothing to do
			if (!$i) {
				return null;
			}

			$curl = \curl_init(self::CSV_API_URL);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, [
				'columns'        => $columns,
				'result_columns' => ['latitude', 'longitude'],
				'data'           => new \CURLFile($tmp, 'text/csv', 'search.csv'),
			]);

			$fp = fopen($destination, 'wb');
			curl_setopt($curl, CURLOPT_FILE, $fp);

			curl_exec($curl);
			$info = curl_getinfo($curl);

			if ($error = curl_error($curl)) {
				throw new UserException(sprintf('L\'API ne répond pas : %s', $error));
			}

			if (200 != ($code = curl_getinfo($curl, CURLINFO_HTTP_CODE))) {
				throw new UserException(sprintf('L\'API a renvoyé une erreur : %d', $code));
			}

			curl_close($curl);
			unset($curl);
			fclose($fp);

			$fp = fopen($destination, 'r');

			$db->begin();
			$i = 0;

			while (!feof($fp)) {
				$row = fgetcsv($fp);

				if (!$i++ || !$row) {
					continue;
				}

				if (!isset($row[4], $row[5])) {
					continue;
				}

				$foreign_hash = substr($row[0], 0, 40);
				$user = $users[$foreign_hash] ?? null;

				if (!$user) {
					continue;
				}

				$db->upsert('plugin_usermap_locations', [
					'id_user'      => $user['id'],
					'address_hash' => $user['hash'],
					'lat'          => $row[4],
					'lon'          => $row[5],
				], ['id_user']);

				unset($users[$foreign_hash]);
			}

			fclose($fp);

			// Save even if we couldn't find an address
			foreach ($users as $user) {
				$db->upsert('plugin_usermap_locations', [
					'id_user'      => $user['id'],
					'address_hash' => $user['hash'],
					'lat'          => null,
					'lon'          => null,
				], ['id_user']);
			}

			$db->commit();

			return $i;
		}
		finally {
			Utils::safe_unlink($tmp);
			Utils::safe_unlink($destination);
		}
	}
}
