<?php
/**
 * запускать после автотестов
 * @author k.vagin
 */
if (file_exists(__DIR__ . '/results.bin')) {
	$stat = unserialize(file_get_contents(__DIR__ . '/results.bin'));

	$stat['suites']--;
	$stat['clear_time'] = round(100*$stat['clear_time'])/100;
	file_put_contents(__DIR__ . '/results.txt',
		"\033[01mTests:\033[0m {$stat['count']}" . PHP_EOL . //  / {$stat['suites']}
		"\033[01mTime:\033[0m {$stat['clear_time']} s"
	);
}
else {
	file_put_contents(__DIR__ . '/results.txt', @file_get_contents(__DIR__ . '/log.txt'));
}