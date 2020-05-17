<?php

namespace Garradin\Plugin\Reservations;

use Garradin\Plugin;
use Garradin\DB;
use Garradin\Config;
use Garradin\UserException;
use KD2\MiniSkel;
use KD2\MiniSkel_Exception;
use DateTime;

use const Garradin\SECRET_KEY;

class Reservations
{
	const COOKIE_NAME = 'reservation';

	public function listSlots()
	{
		return DB::getInstance()->get('SELECT id, * FROM plugin_reservations_creneaux ORDER BY jour, heure;');
	}

	public function listUpcomingBookings()
	{
		$config = Config::getInstance();
		$query = sprintf('SELECT prp.*, strftime(\'%%s\', datetime(date, \'utc\')) AS date, CASE WHEN prp.id_membre THEN m.%s ELSE prp.nom END AS nom
			FROM plugin_reservations_personnes prp
			LEFT JOIN membres m ON m.id = prp.id_membre
			WHERE date >= date(\'now\') ORDER BY date;', $config->get('champ_identite'));
		$bookings = DB::getInstance()->get($query);

		$date = null;
		$hour = null;
		foreach ($bookings as &$booking) {
			$d = DateTime::createFromFormat('U', $booking->date);
			$h = $d->format('Hi');
			$d = $d->format('Ymd');

			if ($date !== $d) {
				$booking->date_change = true;
				$booking->hour_change = true;
				$date = $d;
				$hour = $h;
			}
			elseif ($h !== $hour) {
				$booking->hour_change = true;
				$hour = $h;
			}
		}

		return $bookings;
	}

	/**
	 * Deletes slots that are not in the array
	 */
	public function deleteMissingSlots(array $ids)
	{
		$db = DB::getInstance();
		return $db->exec(sprintf('DELETE FROM plugin_reservations_creneaux WHERE %s;', $db->where('id', 'NOT IN', $ids)));
	}

	public function listUpcomingSlots()
	{
		$slots = DB::getInstance()->get('SELECT *,
			(SELECT COUNT(*) FROM plugin_reservations_personnes prp WHERE creneau = a.id AND date(prp.date) = a.date) AS jauge
			FROM (
				SELECT id, heure, maximum,
				CASE WHEN repetition = 1 AND jour < date(\'now\', \'localtime\') THEN
					date(\'now\', \'localtime\', strftime(\'weekday %w\', jour))
				ELSE
					jour
				END AS date
				FROM plugin_reservations_creneaux prc
				WHERE jour >= date(\'now\', \'localtime\') OR repetition = 1
				ORDER BY date, heure
			) AS a;');

		$date = null;
		$hour_now = date('Hi');
		$day_now = date('Y-m-d');

		foreach ($slots as &$slot) {
			if ($date !== $slot->date) {
				$slot->date_change = true;
				$date = $slot->date;
			}

			$slot_hour = (int) str_replace(':', '', $slot->heure);
			$slot->timestamp = DateTime::createFromFormat('Y-m-d', $slot->date)->getTimestamp();
			$slot->available = $slot->maximum - $slot->jauge;

			if ($day_now == $slot->date && $hour_now > $slot_hour) {
				$slot->bookable = false;
			}
			else {
				$slot->bookable = true;
			}
		}

		return $slots;
	}

	public function deleteSlot(int $id)
	{
		return DB::getInstance()->preparedQuery('DELETE FROM plugin_reservations_personnes WHERE creneau = ?; DELETE FROM plugin_reservations_creneaux WHERE id = ?;', $id, $id);
	}

	public function createSlot(string $day, string $hour, bool $repeat, int $max)
	{
		if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) {
			throw new UserException('Date invalide');
		}

		if (!preg_match('/^\d{2}:\d{2}$/', $hour)) {
			throw new UserException('Heure invalide');
		}

		$db = DB::getInstance();

		$db->preparedQuery('INSERT OR IGNORE INTO plugin_reservations_creneaux (jour, heure, repetition, maximum) VALUES (?, ?, ?, ?);', [$day, $hour, (int)$repeat, abs($max)]);

		return $db->firstColumn('SELECT id FROM plugin_reservations_creneaux WHERE jour = ? AND heure = ?;', $day, $hour);
	}

	public function updateSlot(int $id, string $day, string $hour, bool $repeat, int $max)
	{
		if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $day)) {
			throw new UserException('Date invalide');
		}

		if (!preg_match('/^\d{2}:\d{2}$/', $hour)) {
			throw new UserException('Heure invalide');
		}

		return DB::getInstance()->update('plugin_reservations_creneaux', [
			'jour'       => $day,
			'heure'      => $hour,
			'repetition' => (int)$repeat,
			'maximum'    => abs($max),
		], 'id = :id', ['id' => $id]);
	}

	public function createBooking(int $slot_id, DateTime $date, ?int $id_membre, ?string $nom)
	{
		$db = DB::getInstance();

		$db->preparedQuery('REPLACE INTO plugin_reservations_personnes (creneau, date, id_membre, nom)
			VALUES (?, ?, ?, ?);', [$slot_id, $date, $id_membre, $nom]);

		return $db->lastInsertId();
	}

	public function checkNewBooking(int $slot_id, DateTime &$date, ?int $id_membre, ?string $nom)
	{
		$db = DB::getInstance();

		// Pour qu'une réservation soit valide, il faut qu'elle soit à la date-même
		// ou alors si la répétition est activée, au même jour d'une date ultérieure
		$test = 'id = :id AND (
			(repetition = 1 AND :date >= jour AND strftime(\'%w\', jour) = strftime(\'%w\', :date))
			OR jour = :date)';

		$booking = $db->first('SELECT prc.*, (SELECT COUNT(*) FROM plugin_reservations_personnes prp WHERE creneau = prc.id AND prc.jour = :date) AS jauge FROM plugin_reservations_creneaux prc WHERE ' . $test, ['id' => $slot_id, 'date' => $date->format('Y-m-d')]);

		if (!$booking) {
			throw new UserException('Date ou créneau invalide');
		}

		if ($booking->jauge >= $booking->maximum) {
			throw new UserException('Ce créneau est déjà complet, désolé !');
		}

		$hour = explode(':', $booking->heure);
		$date->setTime($hour[0], $hour[1], 0);

		if ($id_membre && !$db->test('membres', 'id = ?', $id_membre)) {
			throw new UserException('Numéro de membre inconnu');
		}

		if (null === $id_membre && trim($nom) === '') {
			throw new UserException('Le nom doit être renseigné');
		}
	}

	public function deleteBooking(int $id)
	{
		return DB::getInstance()->delete('plugin_reservations_personnes', 'id = ?', $id);
	}

	public function getUserBooking()
	{
		$id = $this->getUserBookingId();

		if (!$id) {
			return null;
		}

		return DB::getInstance()->first('SELECT *, strftime(\'%s\', date, \'utc\') AS date FROM plugin_reservations_personnes WHERE id = ?;', $id);
	}

	protected function getUserBookingId()
	{
		if (empty($_COOKIE[self::COOKIE_NAME])) {
			return null;
		}

		$id = (int) strtok($_COOKIE[self::COOKIE_NAME], '/');
		$hash = strtok('');

		if (!$id || !$hash) {
			return null;
		}

		if ($hash !== hash_hmac('sha256', $id, SECRET_KEY)) {
			return null;
		}

		return $id;
	}

	protected function setUserBooking(int $id, DateTime $expiry)
	{
		$cookie = sprintf('%d/%s', $id, hash_hmac('sha256', $id, SECRET_KEY));
		setcookie(self::COOKIE_NAME, $cookie, $expiry->getTimestamp());
		$_COOKIE[self::COOKIE_NAME] = $cookie;
	}

	public function cancelUserBooking()
	{
		$id = $this->getUserBookingId();

		if (!$id) {
			return;
		}

		setcookie(self::COOKIE_NAME, '', -1);
		unset($_COOKIE[self::COOKIE_NAME]);

		return $this->deleteBooking($id);
	}

	public function createBookingForUser(string $slot_code, ?string $id_membre, ?string $nom)
	{
		$slot_id = (int)strtok($slot_code, '=');
		$date = strtok('');

		try {
			$date = DateTime::createFromFormat('Y-m-d', $date, new \DateTimeZone('UTC'));
		}
		catch (\Exception $e) {
			$date = null;
		}

		if (!$slot_id || !$date) {
			throw new UserException('Erreur dans la date');
		}

		$this->checkNewBooking($slot_id, $date, $id_membre, $nom);

		return $this->createBooking($slot_id, $date, $id_membre, $nom);
	}

	public function createUserBooking(string $slot_code, ?string $id_membre, ?string $nom)
	{
		$slot_id = (int)strtok($slot_code, '=');
		$date = strtok('');

		try {
			$date = DateTime::createFromFormat('Y-m-d', $date, new \DateTimeZone('UTC'));
		}
		catch (\Exception $e) {
			$date = null;
		}

		if (!$slot_id || !$date) {
			throw new UserException('Erreur dans la date');
		}

		$this->checkNewBooking($slot_id, $date, $id_membre, $nom);

		// Cancel old booking
		$this->cancelUserBooking();

		$id = $this->createBooking($slot_id, $date, $id_membre, $nom);

		return $this->setUserBooking($id, $date);
	}

	public function pruneBookings(int $days)
	{
		return DB::getInstance()->preparedQuery('DELETE FROM plugin_reservations_personnes WHERE date < datetime(\'now\', ? || \' days\');', -$days);
	}

	public function __destruct()
	{
		$this->pruneBookings(7);
	}

/*
	static public function boucle(array &$params, array &$return)
	{
		foreach ($params['loopCriterias'] as $criteria)
		{
			if ($criteria['action'] != MiniSkel::ACTION_MATCH_FIELD) {
				continue;
			}

			if ($criteria['field'] == 'futur') {
				// Retourne les prochains créneaux à venir
				$return['query'] = 'SELECT id, heure, maximum,
					CASE WHEN repetition = 1 THEN
						strftime(\'%s\', \'now\', strftime(\'weekday %w\', date))
					ELSE
						strftime(\'%s\', date)
					END AS date,
					(SELECT COUNT(*) FROM plugin_reservations_personnes prp WHERE creneau = prc.id AND pr.date = date) AS jauge,
					maximum - jauge AS places
					FROM plugin_reservations_creneaux prc
					WHERE date >= date() OR repetition = 1
					ORDER BY date, heure;';
			}
			elseif ($criteria['field'] == 'perso') {
				$return['query'] = 'SELECT prp.*, prc.heure FROM plugin_reservations_personnes prp
					INNER JOIN plugin_reservations_creneaux prc ON prc.id = prp.creneau
					WHERE prp.id = ?;';
				$return['query_args'] = [(new Reservations)->getUserBookingId()];
			}
			else {
				throw new MiniSkel_Exception('Critère inconnu');
			}
		}

		$url 
		$return['loop_start'] = sprintf('\$this->variables[\'reservations_form_url\'] = %s;', var_export($url, true));

		return true;
	}
*/
}
