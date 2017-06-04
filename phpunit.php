<?php
set_include_path(dirname(__FILE__).'/'  . PATH_SEPARATOR . get_include_path());

require_once 'src/Suricate/AutoLoader.php';

if (!class_exists('\PHPUnit_Framework_TestCase') && class_exists('\PHPUnit\Framework\TestCase')) {
  class_alias('\PHPUnit\Framework\TestCase', '\PHPUnit_Framework_TestCase');
}

\Suricate\AutoLoader::register();
$app = new Suricate\Suricate();
