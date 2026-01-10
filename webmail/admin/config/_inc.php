<?php

namespace Paheko;

use Paheko\Users\Session;

Session::getInstance()->requireAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN);

