<?php

namespace Garradin\Plugin\HelloAsso\Entities;

use Garradin\DB;
use Garradin\Entity;
use Garradin\ValidationException;

use DateTime;

class Payment extends Entity
{
	const TABLE = 'plugin_helloasso_payments';

	protected int $id;
	protected int $id_order;
	protected int $id_form;
	protected ?int $id_user;
	protected ?int $id_transaction;
	protected int $amount;
	protected string $state;
	protected bool $transferred;
	protected string $person;
	protected \DateTime $date;
	protected ?string $receipt_url;
	protected string $raw_data;

	const STATES = [
		'Pending'               => 'À venir',
		'Authorized'            => 'Autorisé',
		'Refused'               => 'Refusé',
		'Unknown'               => 'Inconnu',
		'Registered'            => 'Enregistré',
		'Error'                 => 'Erreur',
		'Refunded'              => 'Remboursé',
		'Abandoned'             => 'Abandonné',
		'Refunding'             => 'En remboursement',
		'Canceled'              => 'Annulé',
		'Contested'             => 'Contesté',
		'WaitingBankValidation' => 'Attente de validation de la banque',
		'WaitingBankWithdraw'   => 'Attente retrait de la banque',
	];

	const STATE_OK = 'Authorized';

	const CASH_OUT_OK = 'CashedOut';

	const PAYER_FIELDS = [
		'firstName'   => 'Prénom',
		'lastName'    => 'Nom',
		'company'     => 'Organisme',
		'email'       => 'Adresse E-Mail',
		'address'     => 'Adresse postale',
		'zipCode'     => 'Code postal',
		'city'        => 'Ville',
		'country'     => 'Pays',
		'dateOfBirth' => 'Date de naissance',
	];

	static public function getPayerName(\stdClass $payer)
	{
		$names = [!empty($payer->company) ? $payer->company . ' — ' : null, $payer->firstName ?? null, $payer->lastName ?? null];
		$names = array_filter($names);

		$names = implode(' ', $names);

		if (!empty($payer->city)) {
			$names .= sprintf(' (%s)', $payer->city);
		}

		return $names;
	}

	static public function getPayerInfos(\stdClass $payer)
	{
		$data = [];

		foreach (self::PAYER_FIELDS as $key => $name) {
			if (!isset($payer->$key)) {
				continue;
			}

			$value = $payer->$key;

			if ($key == 'dateOfBirth') {
				$value = new \DateTime($value);
			}

			$data[$name] = $value;
		}

		return $data;
	}
}
