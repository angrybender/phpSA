<?php
/**
 *
 * @author k.vagin
 */

if (php_sapi_name() != 'cli') {
	die('Must run from command line');
}

include __DIR__ . '/lib/cli/cli.php';

spl_autoload_register(function($class_name) {
	if (substr($class_name, 0, 4) === 'cli\\') {
		include_once __DIR__ . '/lib/' . str_replace('\\', '/', $class_name) . '.php';
	}
});