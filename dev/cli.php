<?php
/**
 * пример: php dev/cli.php --generator checker --name BothArgumentsIdentical
 * @author k.vagin
 */

include __DIR__ .'/../third_party/cli_tool/autoload.php';

$arguments = new \cli\Arguments();

$arguments->addFlag(array('help', 'h'), 'Show this help screen');

$arguments->addOption(array('G', 'generator'), array(
	'default'     => false,
	'description' => 'Start generator'));

$arguments->addOption(array('N', 'name'), array(
	'default'     => false,
	'description' => 'Name for generator if need'));

$arguments->parse();

if ($arguments['help'] || count($arguments->getArguments()) === 0) {
	echo $arguments->getHelpScreen();
	die (PHP_EOL);
}

if (isset($arguments['G'])) {
	// какой то генератор
	$file = __DIR__ . '/generators/' . $arguments['G'] . '.php';
	if (file_exists($file)) {
		try {
			include_once $file;
		}
		catch (Exception $e) {
			\cli\line(\cli\Colors::colorize('%r >> %w' . $e->getMessage()));
			die();
		}

		\cli\line(\cli\Colors::colorize('%g' . 'done %w')); // todo исправить это уродство или напилить обертку
	}
	else {
		\cli\line(\cli\Colors::colorize('%r >> %w' . 'Generator ' . $arguments['G'] . ' not exist'));
	}
}