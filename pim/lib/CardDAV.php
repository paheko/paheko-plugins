<?php

namespace Paheko\Plugin\PIM;

use Sabre\VObject;

use Sabre\CardDAV as Sabre_CardDAV;
use Sabre\DAV;
use Sabre\CardDAV\Backend\AbstractBackend;
use Sabre\CardDAV\Backend\SyncSupport;

class CardDAV extends AbstractBackend implements SyncSupport
{
	protected Contacts $contacts;

	function __construct(int $user_id)
	{
		$this->contacts = new Contacts($user_id);
	}

	function log($msg)
	{
		//file_put_contents(__DIR__ . '/../../dav.log', sprintf('[%s] CARDDAV: %s' . PHP_EOL, date('d/m/Y H:i:s'), $msg), FILE_APPEND);
	}

	function getAddressBooksForUser($principalUri)
	{
		$synctoken = 0;
		$addressBooks = [];

		$this->log('Return list of address books');

		$addressBooks[] = [
			'id'                                     => 1,
			'uri'                                    => 'contacts',
			'principaluri'                           => $principalUri,
			'{DAV:}displayname'                      => 'Contacts',
			'{http://calendarserver.org/ns/}getctag' => $synctoken,
			'{http://sabredav.org/ns}sync-token'     => $synctoken,
			//'{' . Sabre_CardDAV\Plugin::NS_CARDDAV . '}addressbook-description' => 'Mes contacts',
		];

		return $addressBooks;
	}

	/**
	 * Updates properties for an address book.
	 *
	 * The list of mutations is stored in a Sabre\DAV\PropPatch object.
	 * To do the actual updates, you must tell this object which properties
	 * you're going to process with the handle() method.
	 *
	 * Calling the handle method is like telling the PropPatch object "I
	 * promise I can handle updating this property".
	 *
	 * Read the PropPatch documentation for more info and examples.
	 *
	 * @param string $addressBookId
	 * @param \Sabre\DAV\PropPatch $propPatch
	 * @return void
	 */
	function updateAddressBook($addressBookId, \Sabre\DAV\PropPatch $propPatch)
	{
		return false;
	}

	/**
	 * Creates a new address book
	 *
	 * @param string $principalUri
	 * @param string $url Just the 'basename' of the url.
	 * @param array $properties
	 * @return int Last insert id
	 */
	function createAddressBook($principalUri, $url, array $properties)
	{
		return false;
	}

	/**
	 * Deletes an entire addressbook and all its contents
	 *
	 * @param int $addressBookId
	 * @return void
	 */
	function deleteAddressBook($addressBookId)
	{
		return false;
	}

	/**
	 * Returns all cards for a specific addressbook id.
	 *
	 * This method should return the following properties for each card:
	 *   * carddata - raw vcard data
	 *   * uri - Some unique url
	 *   * lastmodified - A unix timestamp
	 *
	 * It's recommended to also return the following properties:
	 *   * etag - A unique etag. This must change every time the card changes.
	 *   * size - The size of the card in bytes.
	 *
	 * If these last two properties are provided, less time will be spent
	 * calculating them. If they are specified, you can also ommit carddata.
	 * This may speed up certain requests, especially with large cards.
	 *
	 * @param mixed $addressbookId
	 * @return array
	 */
	function getCards($addressbookId)
	{
		$result = [];

		$this->log('returning contacts');

		foreach ($this->contacts->listAll() as $contact)
		{
			$result[] = [
				'etag'         => sprintf('"%s"', $contact->etag()),
				'lastmodified' => $contact->updated,
				'uri'          => $contact->uri,
				'carddata'     => $contact->exportVCard(),
			];
		}

		return $result;
	}

	/**
	 * Returns a specific card.
	 *
	 * The same set of properties must be returned as with getCards. The only
	 * exception is that 'carddata' is absolutely required.
	 *
	 * If the card does not exist, you must return false.
	 *
	 * @param mixed $addressBookId
	 * @param string $cardUri
	 * @return array
	 */
	function getCard($addressBookId, $cardUri)
	{
		$contact = $this->contacts->getFromURI($cardUri);

		if (!$contact) {
			return false;
		}

		return [
			'etag'         => sprintf('"%s"', $contact->etag()),
			'lastmodified' => $contact->updated,
			'uri'          => $contact->uri,
			'carddata'     => $contact->exportVCard(),
		];
	}

	/**
	 * Returns a list of cards.
	 *
	 * This method should work identical to getCard, but instead return all the
	 * cards in the list as an array.
	 *
	 * If the backend supports this, it may allow for some speed-ups.
	 *
	 * @param mixed $addressBookId
	 * @param array $uris
	 * @return array
	 */
	function getMultipleCards($addressBookId, array $uris)
	{
		$all = [];

		foreach ($uris as $uri) {
			$all[] = $this->getCard($addressBookId, $uri);
		}

		return $all;
	}

	/**
	 * Creates a new card.
	 *
	 * The addressbook id will be passed as the first argument. This is the
	 * same id as it is returned from the getAddressBooksForUser method.
	 *
	 * The cardUri is a base uri, and doesn't include the full path. The
	 * cardData argument is the vcard body, and is passed as a string.
	 *
	 * It is possible to return an ETag from this method. This ETag is for the
	 * newly created resource, and must be enclosed with double quotes (that
	 * is, the string itself must contain the double quotes).
	 *
	 * You should only return the ETag if you store the carddata as-is. If a
	 * subsequent GET request on the same card does not have the same body,
	 * byte-by-byte and you did return an ETag here, clients tend to get
	 * confused.
	 *
	 * If you don't return an ETag, you can just return null.
	 *
	 * @param mixed $addressBookId
	 * @param string $cardUri
	 * @param string $cardData
	 * @return string|null
	 */
	function createCard($addressBookId, $cardUri, $cardData)
	{
		$contact = $this->contacts->create();
		$contact->importVCard($cardData);
		$contact->uri = $cardUri;

		$this->log(sprintf('Create contact %s: %s', $cardUri, print_r($contact->asArray(), true)));

		$contact->save();

		return sprintf('"%s"', $contact->etag());
	}

	/**
	 * Updates a card.
	 *
	 * The addressbook id will be passed as the first argument. This is the
	 * same id as it is returned from the getAddressBooksForUser method.
	 *
	 * The cardUri is a base uri, and doesn't include the full path. The
	 * cardData argument is the vcard body, and is passed as a string.
	 *
	 * It is possible to return an ETag from this method. This ETag should
	 * match that of the updated resource, and must be enclosed with double
	 * quotes (that is: the string itself must contain the actual quotes).
	 *
	 * You should only return the ETag if you store the carddata as-is. If a
	 * subsequent GET request on the same card does not have the same body,
	 * byte-by-byte and you did return an ETag here, clients tend to get
	 * confused.
	 *
	 * If you don't return an ETag, you can just return null.
	 *
	 * @param mixed $addressBookId
	 * @param string $cardUri
	 * @param string $cardData
	 * @return string|null
	 */
	function updateCard($addressBookId, $cardUri, $cardData)
	{
		$contact = $this->contacts->getFromURI($cardUri);

		if (!$contact) {
			return false;
		}

		$contact->importVCard($cardData);
		$contact->save();

		$this->log('Update contact: ' . print_r($contact->asArray(), true));

		return sprintf('"%s"', $contact->etag());
	}

	/**
	 * Deletes a card
	 *
	 * @param mixed $addressBookId
	 * @param string $cardUri
	 * @return bool
	 */
	function deleteCard($addressBookId, $cardUri)
	{
		$this->log('Delete contact: ' . $cardUri);
		$contact = $this->contacts->getFromURI($cardUri);

		if (!$contact) {
			return false;
		}

		$contact->delete();
		return true;
	}

	/**
	 * The getChanges method returns all the changes that have happened, since
	 * the specified syncToken in the specified address book.
	 *
	 * This function should return an array, such as the following:
	 *
	 * [
	 *   'syncToken' => 'The current synctoken',
	 *   'added'   => [
	 *      'new.txt',
	 *   ],
	 *   'modified'   => [
	 *      'updated.txt',
	 *   ],
	 *   'deleted' => [
	 *      'foo.php.bak',
	 *      'old.txt'
	 *   ]
	 * ];
	 *
	 * The returned syncToken property should reflect the *current* syncToken
	 * of the addressbook, as reported in the {http://sabredav.org/ns}sync-token
	 * property. This is needed here too, to ensure the operation is atomic.
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
	 * @param string $addressBookId
	 * @param string $syncToken
	 * @param int $syncLevel
	 * @param int $limit
	 * @return array
	 */
	function getChangesForAddressBook($addressBookId, $syncToken, $syncLevel, $limit = null)
	{
		$out = [
			'syncToken' => null,
			'added'     => [],
			'modified'  => [],
			'deleted'   => [],
		];

		if ($syncToken)
		{
			foreach ($this->contacts->listChanges((int) $syncToken) as $change)
			{
				if (is_null($out['syncToken']))
				{
					// Current token
					$out['syncToken'] = $change->timestamp;
				}

				if ($change->type == Contacts::ADDED)
				{
					$out['added'] = $change->uri;
				}
				elseif ($change->type == Contacts::MODIFIED)
				{
					$out['modified'] = $change->uri;
				}
				elseif ($change->type == Contacts::DELETED)
				{
					$out['deleted'] = $change->uri;
				}
			}

			$this->log('Get changes since: ' . $syncToken . PHP_EOL . print_r($out, true));
		}
		else
		{
			$this->log('Get all contacts -- first sync');

			// First sync: everything has been added
			$out['syncToken'] = time();

			foreach ($this->contacts->listAll() as $contact)
			{
				$out['added'] = $contact->uri;
			}
		}

		return $out;
	}

}
