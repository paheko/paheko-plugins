<?php

namespace Paheko;

use Paheko\Web\Router;

$uri = Router::getRequestURI();

if (strpos($uri, '/admin/') === 0) {
	throw new UserException('Page introuvable', 404);
}

require __DIR__ . '/public/index.php';
