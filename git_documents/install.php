<?php

namespace Garradin;

$plugin->registerSignal('files.move', 'Garradin\Plugin\Git_Documents\GitDocuments::sync');
$plugin->registerSignal('files.delete', 'Garradin\Plugin\Git_Documents\GitDocuments::sync');
$plugin->registerSignal('files.store', 'Garradin\Plugin\Git_Documents\GitDocuments::sync');
$plugin->registerSignal('files.mkdir', 'Garradin\Plugin\Git_Documents\GitDocuments::sync');
