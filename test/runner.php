<?php

require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'curl.php';

require_once 'ztest/ztest.php';
require_once 'test_helper.php';

$reporter = new ztest\ConsoleReporter;
$reporter->enable_color();

$suite = new ztest\TestSuite('Curl and CurlResponse unit tests');
$suite->require_all(__DIR__.DIRECTORY_SEPARATOR.'unit');
$suite->auto_fill();
$suite->run($reporter);