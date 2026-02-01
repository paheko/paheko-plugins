<?php

namespace Paheko\Plugin\HelloAsso\Entities;

use Paheko\DB;
use Paheko\Entity;
use Paheko\ValidationException;

use DateTime;

class Item extends Entity
{
	const TABLE = 'plugin_helloasso_items';

	protected int $id;
	protected int $id_order;
	protected int $id_form;
	protected ?int $id_tier = null;
	protected ?int $id_user = null;
	protected ?int $id_subscription = null;
	protected string $type;
	protected string $state;
	protected string $label;
	protected string $person;
	protected int $amount;
	protected ?string $custom_fields;
	protected string $raw_data;

	/**
	 * Says what type of account should be used
	 */
	const TYPES_ACCOUNTS = [
		'Donation'        => 'donation',
		'Payment'         => 'payment',
		'Registration'    => 'payment',
		'Membership'      => 'payment',
		'MonthlyDonation' => 'donation',
		'MonthlyPayment'  => 'payment',
		'OfflineDonation' => 'donation',
		'Contribution'    => 'payment',
		'Bonus'           => 'payment',
		'Product'         => 'payment',
	];

	const TYPES = [
		'Donation'        => 'Don',
		'Payment'         => 'Paiement',
		'Registration'    => 'Inscription',
		'Membership'      => 'Adhésion',
		'MonthlyDonation' => 'Don mensuel',
		'MonthlyPayment'  => 'Paiement mensuel',
		'OfflineDonation' => 'Don hors-ligne',
		'Contribution'    => 'Contribution',
		'Bonus'           => 'Bonus',
		'Product'         => 'Produit',
	];

	const TYPES_COLORS = [
		'Donation'        => 'darkgreen',
		'Payment'         => 'CadetBlue',
		'Registration'    => 'Chocolate',
		'Membership'      => 'CornflowerBlue',
		'MonthlyDonation' => 'DarkOliveGreen',
		'MonthlyPayment'  => 'DarkBlue',
		'OfflineDonation' => 'DarkSeaGreen',
		'Contribution'    => 'DarkSlateBlue',
		'Bonus'           => 'DarkSlateGray',
		'Product'         => 'CadetBlue',
	];

	const STATES = [
		'Waiting'    => 'En attente',
		'Processed'  => 'Traité',
		'Registered' => 'Enregistré',
		'Deleted'    => 'Supprimé',
		'Refunded'   => 'Remboursé',
		'Unknown'    => 'Inconnu',
		'Canceled'   => 'Annulé',
		'Contested'  => 'Contesté',
		'Unknow'     => 'Inconnu', // Typo de l'API HelloASSO
		'Refused'    => 'Refusé',
		'Abandoned'  => 'Abandonné',
	];

	public function setUserId(int $id): void
	{
		if ($this->id_user) {
			return;
		}

		$this->set('id_user', $id);
		$this->save();
	}
}
