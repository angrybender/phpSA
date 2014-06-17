<?php

if (php_sapi_name() != 'cli') {
	die('Must run from command line');
}

include __DIR__ . '/../lib/cli/cli.php';

spl_autoload_register(function($class_name) {
	include_once __DIR__ . '/../lib/' . str_replace('\\', '/', $class_name) . '.php';
});

function test_notify(cli\Notify $notify, $cycle = 1000000, $sleep = null) {
	for ($i = 0; $i <= $cycle; $i++) {
		$notify->tick();
		if ($sleep) usleep($sleep);
	}
	$notify->finish();
}
