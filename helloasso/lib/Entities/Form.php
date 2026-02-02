<?php

namespace Paheko\Plugin\HelloAsso\Entities;

use Paheko\DB;
use Paheko\Entity;
use Paheko\ValidationException;

use KD2\DB\EntityManager as EM;

use DateTime;

class Form extends Entity
{
	const TABLE = 'plugin_helloasso_forms';

	protected int $id;
	protected string $org_slug;
	protected string $org_name;
	protected string $name;
	protected string $state;

	protected string $type;
	protected string $slug;

	protected string $raw_data;

	protected ?int $id_year;
	protected ?string $payment_account_code;

	const TYPES = [
		'CrowdFunding' => 'Crowdfunding',
		'Membership'   => 'Adhésion',
		'Event'        => 'Billetteries',
		'Donation'     => 'Dons',
		'PaymentForm'  => 'FlexiPaiement',
		'Checkout'     => 'Encaissement',
		'Shop'         => 'Boutique',
	];

	const STATES = [
		'Draft'    => 'brouillon',
		'Public'   => 'public',
		'Private'  => 'privé',
		'Disabled' => 'désactivé',
	];

	const STATES_COLORS = [
		'Draft'    => 'darkgray',
		'Public'   => 'darkgreen',
		'Private'  => 'darkred',
		'Disabled' => 'black',
	];

	public function listTiers(): array
	{
		return EM::getInstance(Tier::class)->all('SELECT * FROM @TABLE WHERE id_form = ? ORDER BY type, label COLLATE U_NOCASE, amount;', $this->id());
	}

	public function listOptions(): array
	{
		return EM::getInstance(Option::class)->all('SELECT * FROM @TABLE WHERE id_form = ? ORDER BY label COLLATE U_NOCASE, amount;', $this->id());
	}

	public function importForm(?array $source = null)
	{
		$source ??= $_POST;

		if (isset($source['payment_account_code']) && is_array($source['payment_account_code'])) {
			$source['payment_account_code'] = key($source['payment_account_code']);
		}

		parent::importForm($source);
	}
}
