<?php

namespace Paheko\Plugin\Invoice;

use Paheko\UserException;
use Paheko\Utils;
use Paheko\Users\Session;

use Paheko\Plugin\Invoice\Entities\Client;

use const Paheko\PLUGIN_ROOT;

Session::getInstance()->requireAccess(Session::SECTION_ACCOUNTING, Session::ACCESS_WRITE);

$tpl->display(PLUGIN_ROOT . '/templates/clients/from_user.tpl');
