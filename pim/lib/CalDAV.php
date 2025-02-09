<?php

namespace Paheko\Plugin\PIM;

use Sabre\CalDAV as Sabre_CalDAV;
use Sabre\DAV;
use Sabre\CalDAV\Backend\AbstractBackend;
use Sabre\CalDAV\Backend\SyncSupport;
use Sabre\VObject;

class CalDAV extends AbstractBackend implements SyncSupport
{
	protected Events $events;

	function __construct(int $user_id)
	{
		$this->events = new Events($user_id);
	}

	function log($msg)
	{
		//file_put_contents(__DIR__ . '/../../dav.log', sprintf('[%s] %s' . PHP_EOL, date('d/m/Y H:i:s'), $msg), FILE_APPEND);
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
		foreach ($this->events->listCategories() as $row)
		{
			$calendars[] = [
				'id'           => $row->id,
				'uri'          => $row->id . '-' . preg_replace('/[^\w]/', '_', $row->title),
				'{DAV:}displayname' => $row->title,
				'principaluri' => $principalUri,
				'{' . Sabre_CalDAV\Plugin::NS_CALDAV . '}supported-calendar-component-set' => new Sabre_CalDAV\Xml\Property\SupportedCalendarComponentSet(['VEVENT']),
				// Ignore sync tokens for now!
				//'{' . Sabre_CalDAV\Plugin::NS_CALENDARSERVER . '}getctag'                  => 'http://sabre.io/ns/sync/1',
				//'{http://sabredav.org/ns}sync-token'                                 => '1',
				'{http://apple.com/ns/ical/}calendar-color' => utils::hsl2rgb($row->color, 50, 75),
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
			$data = $event->serialize();

			$result[] = [
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

		$data = $event->serialize();

		$this->log($event->title);
		$this->log(print_r($event->date, true));
		$this->log('updated: ' . $event->updated);

		return [
			'uri'          => $event->uri,
			'etag'         => sprintf('"%s"', $event->etag()),
			'calendarid'   => $calendarId,
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

		$event = $this->events->createFromVCalendar($calendarData, $calendarId, $objectUri);
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

		$event->importVCalendar($calendarData);

		// Not sure what this does??
		if ($data->date->getTimezone()->getName() == 'UTC')
		{
			$event = $this->events->get($objectUri);
			$tz = $event->timezone;
			$data->date->setTimeZone(new \DateTimeZone($tz));
			$data->date_end->setTimeZone(new \DateTimeZone($tz));
		}

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


	/**
	 * The getChanges method returns all the changes that have happened, since
	 * the specified syncToken in the specified calendar.
	 *
	 * This function should return an array, such as the following:
	 *
	 * [
	 *   'syncToken' => 'The current synctoken',
	 *   'added'   => [
	 *      'new.txt',
	 *   ],
	 *   'modified'   => [
	 *      'modified.txt',
	 *   ],
	 *   'deleted' => [
	 *      'foo.php.bak',
	 *      'old.txt'
	 *   ]
	 * ];
	 *
	 * The returned syncToken property should reflect the *current* syncToken
	 * of the calendar, as reported in the {http://sabredav.org/ns}sync-token
	 * property this is needed here too, to ensure the operation is atomic.
	 *
	 * If the $syncToken argument is specified as null, this is an initial
	 * sync, and all members should be reported.
	 *
	 * The modified property is an array of nodenames that have changed since
	 * the last token.
	 *
	 * The deleted property is an array with nodenames, that have been deleted
	 * from collection.
	 *
	 * The $syncLevel argument is basically the 'depth' of the report. If it's
	 * 1, you only have to report changes that happened only directly in
	 * immediate descendants. If it's 2, it should also include changes from
	 * the nodes below the child collections. (grandchildren)
	 *
	 * The $limit argument allows a client to specify how many results should
	 * be returned at most. If the limit is not specified, it should be treated
	 * as infinite.
	 *
	 * If the limit (infinite or not) is higher than you're willing to return,
	 * you should throw a Sabre\DAV\Exception\TooMuchMatches() exception.
	 *
	 * If the syncToken is expired (due to data cleanup) or unknown, you must
	 * return null.
	 *
	 * The limit is 'suggestive'. You are free to ignore it.
	 *
	 * @param mixed $calendarId
	 * @param string $syncToken
	 * @param int $syncLevel
	 * @param int $limit
	 * @return array
	 */
	function getChangesForCalendar($calendarId, $syncToken, $syncLevel, $limit = null)
	{
		if (!is_array($calendarId)) {
			throw new \InvalidArgumentException('The value passed to $calendarId is expected to be an array with a calendarId and an instanceId');
		}

		list($calendarId, $instanceId) = $calendarId;

		$this->log('returning changes since ' . $syncToken);

		$out = [
			'syncToken' => null,
			'added'     => [],
			'modified'  => [],
			'deleted'   => [],
		];

		if ($syncToken)
		{
			foreach ($this->agenda->listChangesForCategory($calendarId, (int) $syncToken) as $change)
			{
				if (is_null($out['syncToken']))
				{
					// Current token
					$out['syncToken'] = $change->timestamp;
				}

				if ($change->type == Agenda::ADDED)
				{
					$out['added'] = $change->uri;
				}
				elseif ($change->type == Agenda::MODIFIED)
				{
					$out['modified'] = $change->uri;
				}
				elseif ($change->type == Agenda::DELETED)
				{
					$out['deleted'] = $change->uri;
				}
			}
		}
		else
		{
			// First sync: everything has been added
			$out['syncToken'] = time();

			foreach ($this->agenda->listForCategory($calendarId) as $event)
			{
				$out['added'] = $event->uri;
			}
		}

		return $out;
	}
}
