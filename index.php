<?php

ini_set('memory_limit','256M');
ini_set("session.cache_expire", 600);
ini_set("session.gc_maxlifetime", 36000);
ini_set("session.cookie_lifetime", 36000);
ini_set("pcre.backtrack_limit","9000000");

$f3=require('lib/base.php');
$f3->config('config.ini');
$f3->config('routes.ini');

$logger = new Log('/logs/logs.log');

function filter_escape($input) {
		$input = preg_replace('/([\,;])/','\\\$1', $input);
		$input = str_replace("\n", "\\n", $input);
		$input = str_replace("\r", "\\r", $input);
		return $input;
}


$f3->run();