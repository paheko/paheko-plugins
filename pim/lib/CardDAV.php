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
}
