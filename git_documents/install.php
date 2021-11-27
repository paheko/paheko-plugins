<?php

namespace Garradin;

$plugin->registerSignal('files.move', 'Garradin\Plugin\GitDocuments\GitDocuments::sync');
$plugin->registerSignal('files.delete', 'Garradin\Plugin\GitDocuments\GitDocuments::sync');
$plugin->registerSignal('files.store', 'Garradin\Plugin\GitDocuments\GitDocuments::sync');
$plugin->registerSignal('files.mkdir', 'Garradin\Plugin\GitDocuments\GitDocuments::sync');
