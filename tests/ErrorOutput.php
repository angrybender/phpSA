<?php
/**
 *
 * @author k.vagin
 */

$lines = explode(PHP_EOL, file_get_contents(__DIR__ . '/log.txt'));
array_shift($lines);

$is_start_error = false;
foreach ($lines as $line) {

	if (trim($line) === '---' || trim($line) === '...') continue;

	if (substr($line, 0, 6) === 'not ok' && !$is_start_error) {
		$is_start_error = true;
		echo "\t", preg_replace("/not ok(.+)-\s/", '', $line), PHP_EOL;
	}
	else {
		if ($is_start_error && substr($line, 0, 2) !== 'ok' && substr($line, 0, 1) !== 'no' ) {
			echo "\t", $line, PHP_EOL;
		}
		else {
			$is_start_error = false;
		}
	}
}