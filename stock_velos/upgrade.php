<?php

namespace Garradin;

use Garradin\Plugin\Stock_Velos\Velos;

$plugin->registerSignal('usertemplate.init', [Velos::class, 'register']);
