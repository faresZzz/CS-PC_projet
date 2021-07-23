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

\Middleware::instance()->before('GET|HEAD|POST|PUT|PATCH|DELETE|CONNECT|OPTIONS /views/*', function(\Base $f3, $params, $alias) {
	$Agent = $f3->get('AGENT');
	if( $f3->get('SESSION.username') === null || $f3->get('SESSION.password') === null || $Agent != $f3->get('SESSION.Agent') ) {
		//
		$logger = new \Log('/logs/session.log');
		$logger->write('Acces non autorisé IP :'.$f3->get('IP').' | Route : '.$f3->get('PATH'));
		//
		\Flash::instance()->addMessage('Accès non autorisé !', 'danger');
		sleep(5);
		$f3->reroute('/');
		exit;
	}
	else {
			$f3->set('ONERROR',function($f3){
				$Error = $f3->get('ERROR.code');
				if ( $Error == "405" ) {
					$f3->reroute('/');
				}
			});
	}
});
\Middleware::instance()->run();

$f3->run();