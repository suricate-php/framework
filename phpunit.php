<?php
set_include_path(dirname(__FILE__).'/'  . PATH_SEPARATOR . get_include_path());

require_once 'src/Suricate/AutoLoader.php';

\Suricate\AutoLoader::register();
$app = new Suricate\Suricate();
