<?php

namespace Garradin;

$plugin->registerSignal('boucle.velos', ['Garradin\Velos_Signaux', 'LoopVelos']);

$db = DB::getInstance();
