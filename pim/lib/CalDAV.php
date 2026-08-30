<?php

namespace Paheko\Plugin\PIM;

use Sabre\CalDAV as Sabre_CalDAV;
use Sabre\DAV;
use Sabre\CalDAV\Backend\AbstractBackend;
use Sabre\CalDAV\Backend\SyncSupport;
use Sabre\VObject;

use Paheko\Utils;

class CalDAV extends AbstractBackend implements SyncSupport
{
	protected Events $events;

	function __construct(int $user_id)
	{
		$this->events = new Events($user_id);
	}

	function log($msg)
	{
		//file_put_contents(__DIR__ . '/dav.log', sprintf('[%s] %s' . PHP_EOL, date('d/m/Y H:i:s'), $msg), FILE_APPEND);
	}

	/**
	 * Returns a list of calendars for a principal.
	 *
	 * Every project is an array with the following keys:
	 *  * id, a unique id that will be used by other functions to modify the
	 *    calendar. This can be the same as the uri or a database key.
	 *  * uri. This is just the 'base uri' or 'filename' of the calendar.
	 *  * principaluri. The owner of the calendar. Almost always the same as
	 *    principalUri passed to this method.
	 *
	 * Furthermore it can contain webdav properties in clark notation. A very
	 * common one is '{DAV:}displayname'.
	 *
	 * Many clients also require:
	 * {urn:ietf:params:xml:ns:caldav}supported-calendar-component-set
	 * For this property, you can just return an instance of
	 * Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet.
	 *
	 * If you return {http://sabredav.org/ns}read-only and set the value to 1,
	 * ACL will automatically be put in read-only mode.
	 *
	 * @param string $principalUri
	 * @return array
	 */
	function getCalendarsForUser($principalUri) {
		$this->log('List calendars: ' . $principalUri);

		$calendars = [];
		foreach ($this->events->listCategories() as $row) {
			$calendars[] = [
				'id'           => $row->id,
				'uri'          => $row->id . '-' . strtolower(Utils::transliterateToAscii($row->title)),
				'principaluri' => $principalUri,
				'{DAV:}displayname' => $row->title,
				'{http://apple.com/ns/ical/}calendar-color' => Utils::hsl2rgb($row->color, 50, 75),
				'{' . Sabre_CalDAV\Plugin::NS_CALDAV . '}supported-calendar-component-set' => new Sabre_CalDAV\Xml\Property\SupportedCalendarComponentSet(['VEVENT']),
				// Ignore sync tokens for now!
				//'{' . Sabre_CalDAV\Plugin::NS_CALENDARSERVER . '}getctag'                  => 'http://sabre.io/ns/sync/1',
				//'{http://sabredav.org/ns}sync-token'                                 => '1',
			];

		}

		return $calendars;
	}

	/**
	 * Creates a new calendar for a principal.
	 *
	 * If the creation was a success, an id must be returned that can be used
	 * to reference this calendar in other methods, such as updateCalendar.
	 *
	 * @param string $principalUri
	 * @param string $calendarUri
	 * @param array $properties
	 * @return string
	 */
	function createCalendar($principalUri, $calendarUri, array $properties)
	{
		return false;
	}

	/**
	 * Delete a calendar and all it's objects
	 *
	 * @param string $calendarId
	 * @return void
	 */
	function deleteCalendar($calendarId)
	{
		return false;
	}

	/**
	 * Returns all calendar objects within a calendar.
	 *
	 * Every item contains an array with the following keys:
	 *   * calendardata - The iCalendar-compatible calendar data
	 *   * uri - a unique key which will be used to construct the uri. This can
	 *     be any arbitrary string, but making sure it ends with '.ics' is a
	 *     good idea. This is only the basename, or filename, not the full
	 *     path.
	 *   * lastmodified - a timestamp of the last modification time
	 *   * etag - An arbitrary string, surrounded by double-quotes. (e.g.:
	 *   '  "abcdef"')
	 *   * size - The size of the calendar objects, in bytes.
	 *   * component - optional, a string containing the type of object, such
	 *     as 'vevent' or 'vtodo'. If specified, this will be used to populate
	 *     the Content-Type header.
	 *
	 * Note that the etag is optional, but it's highly encouraged to return for
	 * speed reasons.
	 *
	 * The calendardata is also optional. If it's not returned
	 * 'getCalendarObject' will be called later, which *is* expected to return
	 * calendardata.
	 *
	 * If neither etag or size are specified, the calendardata will be
	 * used/fetched to determine these numbers. If both are specified the
	 * amount of times this is needed is reduced by a great degree.
	 */
	function getCalendarObjects($calendarId): array
	{
		$result = [];

		foreach ($this->events->listForCategory($calendarId) as $event)
		{
			$data = $event->exportVCalendar();

			$result[] = [
				'id'           => $event->id,
				'uri'          => $event->uri,
				'etag'         => sprintf('"%s"', $event->etag()),
				'calendarid'   => $calendarId,
				'size'         => strlen($data),
				'calendardata' => $data,
				'lastmodified' => $event->updated,
			];
		}

		$this->log('returning ' . count($result) . ' events for calendar ' . $calendarId);
		return $result;
	}

	/**
	 * Returns information from a single calendar object, based on it's object
	 * uri.
	 *
	 * The object uri is only the basename, or filename and not a full path.
	 *
	 * The returned array must have the same keys as getCalendarObjects. The
	 * 'calendardata' object is required here though, while it's not required
	 * for getCalendarObjects.
	 *
	 * This method must return null if the object did not exist.
	 *
	 * @param string $calendarId
	 * @param string $objectUri
	 * @return array|null
	 */
	function getCalendarObject($calendarId, $objectUri): ?array
	{
		$this->log('get object ' . $objectUri);
		$event = $this->events->getFromURI($objectUri);

		if (!$event) {
			return null;
		}

		$data = $event->exportVCalendar();

		$this->log($event->title);
		$this->log(print_r($event->start, true));

		return [
			'id'           => $event->id,
			'uri'          => $event->uri,
			'etag'         => sprintf('"%s"', $event->etag()),
			'calendarid'   => $calendarId,
			'size'         => strlen($data),
			'calendardata' => $data,
			'lastmodified' => $event->updated,
		];
	}

	/**
	 * Creates a new calendar object.
	 *
	 * The object uri is only the basename, or filename and not a full path.
	 *
	 * It is possible return an etag from this function, which will be used in
	 * the response to this PUT request. Note that the ETag must be surrounded
	 * by double-quotes.
	 *
	 * However, you should only really return this ETag if you don't mangle the
	 * calendar-data. If the result of a subsequent GET to this object is not
	 * the exact same as this request body, you should omit the ETag.
	 *
	 * @param mixed $calendarId
	 */
	function createCalendarObject($calendarId, $objectUri, $calendarData): ?string
	{
		$this->log('create ' . $objectUri);

		$id = intval(strtok($calendarId, '-'));
		strtok('');

		$event = $this->events->create();
		$event->importVEvent($calendarData);
		$event->uri = $objectUri;
		$event->id_category = $id;
		$event->save();

		return sprintf('"%s"', $event->etag());
	}

	/**
	 * Updates an existing calendarobject, based on it's uri.
	 *
	 * The object uri is only the basename, or filename and not a full path.
	 *
	 * It is possible return an etag from this function, which will be used in
	 * the response to this PUT request. Note that the ETag must be surrounded
	 * by double-quotes.
	 *
	 * However, you should only really return this ETag if you don't mangle the
	 * calendar-data. If the result of a subsequent GET to this object is not
	 * the exact same as this request body, you should omit the ETag.
	 *
	 * @param mixed $calendarId
	 */
	function updateCalendarObject($calendarId, $objectUri, $calendarData): ?string
	{
		$event = $this->events->getFromURI($objectUri);

		if (!$event) {
			return null;
		}

		$event->importVEvent($calendarData);
		$event->save();

		return sprintf('"%s"', $event->etag());
	}

	/**
	 * Deletes an existing calendar object.
	 *
	 * The object uri is only the basename, or filename and not a full path.
	 */
	function deleteCalendarObject($calendarId, $objectUri): void
	{
		$event = $this->events->getFromURI($objectUri);

		if ($event) {
			$event->delete();
		}
	}
}
