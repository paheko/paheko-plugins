<?php

namespace Paheko\Plugin\Shortcuts\Entities;

use Paheko\Entity;
use Paheko\Plugins;
use Paheko\Utils;
use Paheko\UserException;
use Paheko\Files\Files;

use KD2\HTTP;

class Shortcut extends Entity
{
	const TABLE = 'plugin_shortcuts_shortcuts';

	protected ?int $id = null;
	protected string $label;
	protected string $url;
	protected ?string $shape = 'help';
	protected ?string $restrict_section = null;
	protected ?string $restrict_level = null;
	protected bool $icon = false;
	protected int $sort_order = 0;
	protected bool $home = false;
	protected bool $menu = false;
	protected bool $iframe = false;

	public function selfCheck(): void
	{
		$this->assert(mb_strlen($this->label), 'Le libellé doit être renseigné');
		$this->assert(mb_strlen($this->label) <= 60, 'Le libellé doit faire moins de 60 caractères');
		$this->assert(strlen($this->url), 'L\'adresse URL doit être renseigné');
		$this->assert(strlen($this->url) <= 1000, 'L\'adresse URL doit faire moins de 1000 caractères');

		$this->assert(filter_var($this->url, FILTER_VALIDATE_URL), 'L\'adresse URL indiquée est invalide');

		$scheme = parse_url($this->url, PHP_URL_SCHEME);
		$this->assert($scheme === 'http' || $scheme === 'https', 'L\'adresse URL indiquée est invalide');

		if ($this->isModified('iframe') && $this->iframe) {
			$this->assert($this->canUseIframe(), 'L\'adresse URL indiquée n\'autorise pas son utilisation dans un cadre intégré');
		}

		if (isset($this->shape)) {
			$this->assert(array_key_exists($this->shape, Utils::ICONS), 'L\'icône sélectionnée n\'existe pas');
		}

		parent::selfCheck();
	}


	public function importForm(?array $source = null)
	{
		$source ??= $_POST;

		if (isset($source['home_present'])) {
			$source['home'] = !empty($source['home']);
		}

		if (isset($source['menu_present'])) {
			$source['menu'] = !empty($source['menu']);
		}

		if (isset($source['iframe_present'])) {
			$source['iframe'] = !empty($source['iframe']);
		}

		if (!empty($source['delete_icon'])) {
			$source['icon'] = '0';
		}

		return parent::importForm($source);
	}

	public function getIconPath(): ?string
	{
		return Plugins::getStorageRoot('shortcuts') . '/' . $this->id();
	}

	public function save(bool $selfcheck = true): bool
	{
		if (!$this->icon && $this->isModified('icon')) {
			$this->deleteIcon();
		}

		return parent::save($selfcheck);
	}

	public function delete(): bool
	{
		$this->deleteIcon();
		return parent::delete();
	}

	public function deleteIcon(): void
	{
		$f = Files::get($this->getIconPath());

		if (!$f) {
			return;
		}

		$f->delete();
		$this->set('shape', 'help');
	}

	public function uploadIcon(): void
	{
		if (empty($_FILES['icon'])) {
			return;
		}

		$f = Files::upload(Plugins::getStorageRoot('shortcuts'), 'icon', null, $this->id());

		if (!$f) {
			throw new UserException('Erreur à l\'envoi');
		}

		if (!$f->isImage()) {
			$f->delete();
			throw new UserException('Le fichier n\'est pas une image');
		}

		if ($f->mime !== 'image/svg+xml') {
			$i = $f->asImageObject();

			if ($f->isImageTooLarge($i)) {
				$f->delete();
				throw new UserException('Cette image est trop grande. Taille maximale : 6000x6000.');
			}

			try {
				$format = 'png';
				$i->cropResize(100, 100);
				$f->setContent($i->output($format, true));
			}
			catch (\UnexpectedValueException $e) {
				$f->delete();
				throw new UserException('Cet format d\'image n\'est pas supporté.', 0, $e);
			}
		}

		$this->set('icon', true);
		$this->set('shape', null);
		$this->saveOnly(['icon']);
	}

	public function getIconURL(): ?string
	{
		if (!$this->icon) {
			return null;
		}

		$f = Files::get($this->getIconPath());

		if (!$f) {
			return null;
		}

		return $f->url() . '?' . $f->modified->getTimestamp();
	}

	public function canUseIframe(): bool
	{
		$http = new HTTP;
		$r = $http->request('HEAD', $this->url);

		if ($r->fail) {
			return false;
		}

		if ($r->status !== 200) {
			return false;
		}

		if (!empty($r->headers['x-frame-options'])) {
			return false;
		}

		if (!empty($r->headers['Content-Security-Policy'])
			&& preg_match('/frame-ancestors/i', $r->headers['Content-Security-Policy'])) {
			return false;
		}

		return true;
	}

	public function getURL(): string
	{
		if ($this->iframe) {
			return Plugins::getPrivateURL('shortcuts', 'iframe.php?id=' . $this->id());
		}

		return $this->url;
	}

	public function getTarget(): string
	{
		return $this->iframe ? '' : '_blank';
	}
}
