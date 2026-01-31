<?php

namespace Paheko\Plugin\HelloAsso\Entities;

use Paheko\DB;
use Paheko\Entity;
use Paheko\ValidationException;

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

	const TYPES = [
		'CrowdFunding' => 'Crowdfunding',
		'Membership'   => 'Adhésion',
		'Event'        => 'Billetteries',
		'Donation'     => 'Dons',
		'PaymentForm'  => 'Ventes',
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
}
