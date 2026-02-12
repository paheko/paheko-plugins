<?php

namespace Paheko\Plugin\HelloAsso;

use Paheko\Plugin\HelloAsso\Entities\Form;
use Paheko\Plugin\HelloAsso\Entities\Tier;
use Paheko\Plugin\HelloAsso\Entities\Option;
use Paheko\Plugin\HelloAsso\API;
use Paheko\Plugin\HelloAsso\HelloAsso;

use Paheko\DB;

use KD2\DB\EntityManager as EM;

class Forms
{
	static protected ?array $forms_ids;
	static protected ?array $forms_names;

	static public function get(int $id): ?Form
	{
		return EM::findOneById(Form::class, $id);
	}

	static public function getTier(int $id): ?Tier
	{
		return EM::findOneById(Tier::class, $id);
	}

	static public function getOrCreateTier(int $id, int $id_form, ?string $label, ?int $amount, string $type): Tier
	{
		static $tiers = [];

		$tiers[$id] ??= self::getTier($id);

		if ($tiers[$id]) {
			return $tiers[$id];
		}

		$tier = new Tier;
		$tier->import(compact('id', 'id_form', 'label', 'amount', 'type'));
		$tier->id($id);
		$tier->save();

		$tiers[$id] = $tier;
		return $tier;
	}

	static public function getOption(int $id): ?Option
	{
		return EM::findOneById(Option::class, $id);
	}

	static public function getId(string $org_slug, string $form_slug): ?int
	{
		if (!isset(self::$forms_ids)) {
			self::$forms_ids = DB::getInstance()->getAssoc(sprintf('SELECT org_slug || \'__\' || slug, id FROM %s;', Form::TABLE));
		}

		$slug = sprintf('%s__%s', $org_slug, $form_slug);
		return self::$forms_ids[$slug] ?? null;
	}

	static public function getName(int $id): ?string
	{
		if (!isset(self::$forms_names)) {
			self::$forms_names = DB::getInstance()->getAssoc(sprintf('SELECT id, name FROM %s;', Form::TABLE));
		}

		return self::$forms_names[$id] ?? null;
	}

	static public function listByType(?string $type): array
	{
		$params = [];
		$where = '';

		if ($type) {
			$where = 'WHERE type = ?';
			$params[] = $type;
		}

		$sql = sprintf('SELECT f.*, y.label AS year_label
			FROM %s AS f
			LEFT JOIN acc_years y ON y.id = f.id_year %s
			ORDER BY f.state = \'Disabled\', f.type, f.org_name COLLATE NOCASE, f.name COLLATE U_NOCASE;', Form::TABLE, $where);
		$list = DB::getInstance()->get($sql, ...$params);

		foreach ($list as &$row) {
			$row->state_label = Form::STATES[$row->state] ?? '';
			$row->type_label = Form::TYPES[$row->type] ?? '';
			$row->state_color = Form::STATES_COLORS[$row->state] ?? '';
		}

		return $list;
	}

	static public function listOrganizations(): array
	{
		return DB::getInstance()->getAssoc(sprintf('SELECT org_slug, org_name FROM %s GROUP BY org_slug ORDER BY org_slug;', Form::TABLE));
	}

	/**
	 * Synchronizes remote list of forms with local DB
	 * @return void
	 */
	static public function sync(): void
	{
		$api = API::getInstance();
		$organizations = $api->listOrganizations();

		$db = DB::getInstance();
		$db->begin();

		foreach ($organizations as $org) {
			$existing = [];
			$list = EM::getInstance(Form::class)->all('SELECT * FROM @TABLE WHERE org_slug = ?;', $org->organizationSlug);

			foreach ($list as $form) {
				$existing[$form->slug] = $form;
			}

			unset($list);

			$forms = $api->listForms($org->organizationSlug);

			foreach ($forms as $form) {
				$data = $existing[$form->formSlug] ?? new Form;
				$data->org_name = $org->name;
				$data->org_slug = $org->organizationSlug;

				$data->name = strip_tags($form->privateTitle ?? $form->title);
				$data->type = $form->formType;
				$data->state = $form->state;
				$data->slug = $form->formSlug;

				$form = $api->getForm($org->organizationSlug, $data->type, $data->slug);

				$data->raw_data = json_encode($form);
				$data->save();

				// Import tiers and options
				foreach ($form->tiers ?? [] as $tier) {
					$t = EM::findOneById(Tier::class, $tier->id) ?? new Tier;
					$t->id ??= $tier->id;
					$t->id_form = $data->id();
					$t->label = $tier->label ?? null;
					$t->amount = $tier->price ?? null;
					$t->type = $tier->tierType;
					$custom_fields = [];

					foreach ($tier->customFields ?? [] as $field) {
						$custom_fields[$field->id] = $field->label;
					}

					$t->custom_fields = $custom_fields;
					$t->save();

					foreach ($tier->extraOptions ?? [] as $option) {
						$o = EM::findOneById(Option::class, $option->id) ?? new Option;
						$o->id ??= $option->id;
						$o->id_form = $data->id();
						$o->label = $option->label ?? null;
						$o->amount = $option->price ?? null;
						$o->save();

						// Add link between option and tier
						$o->linkTo($tier->id);
					}
				}

			}
		}

		$db->commit();
	}
}
