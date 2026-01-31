<?php

namespace Paheko;

// Files will be deleted by Plugin.php automagically!

$db = DB::getInstance();
$db->import(__DIR__ . '/uninstall.sql');
