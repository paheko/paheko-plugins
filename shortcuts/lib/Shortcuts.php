<?php

namespace Paheko\Plugin\Shortcuts;

use Paheko\Plugin\Shortcuts\Entities\Shortcut;

use Paheko\DB;
use Paheko\Plugins;
use Paheko\Entities\Plugin;
use Paheko\Entities\Signal;
use Paheko\UserTemplate\CommonFunctions;

use KD2\DB\EntityManager as EM;

class Shortcuts
{
	static protected ?array $list = null;

	static public function homeButtons(Signal $signal, Plugin $plugin): void
	{
		$i = 0;

		foreach (self::list() as $row) {
			if (!$row->home) {
				continue;
			}
			$html = CommonFunctions::linkbutton([
				'label' => $row->label,
				'icon' => $row->icon ? $row->getIconURL() : null,
				'shape' => $row->icon ? null : $row->shape,
				'href' => $row->getURL(),
				'target' => $row->getTarget(),
			]);

			$signal->setOut('shortcuts_' . $row->id(), $html);
		}
	}

	static public function menuItems(Signal $signal, Plugin $plugin): void
	{
		foreach (self::list() as $row) {
			if (!$row->menu) {
				continue;
			}

			$signal->setOut('shortcuts_' . $row->id(), sprintf('<a href="%s" target="%s">%s</a>',
				htmlspecialchars($row->getURL()),
				htmlspecialchars($row->getTarget()),
				htmlspecialchars($row->label)
			));
		}
	}

	static public function list(): array
	{
		self::$list ??= EM::getInstance(Shortcut::class)->all('SELECT * FROM @TABLE ORDER BY sort_order;');
		return self::$list;
	}

	static public function get(int $id): ?Shortcut
	{
		return EM::findOneById(Shortcut::class, $id);
	}

	static public function saveSortOrder(array $order): void
	{
		$db = DB::getInstance();
		$db->begin();

		foreach ($order as $index => $id) {
			$db->update(Shortcut::TABLE, ['sort_order' => (int) $index], 'id = ' . (int)$id);
		}

		$db->commit();
	}

	static public function create(): Shortcut
	{
		$s = new Shortcut;
		$s->set('sort_order', DB::getInstance()->firstColumn('SELECT MAX(sort_order) + 1 FROM plugin_shortcuts_shortcuts;'));
		return $s;
	}
}
