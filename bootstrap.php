<?php
/**
 * todo autoloader
 * @author k.vagin
 */

ini_set('memory_limit', '2048M');

include 'third_party/php_parser/lib/bootstrap.php';

include 'analisator/def.types_of_checkers.php';

include 'analisator/ParentChecker.php';
include 'analisator/ParentExtractor.php';
include 'analisator/ParentWorker.php';
include 'analisator/ParentHook.php';

include 'analisator/Report.php';
include 'analisator/Config.php';
include 'analisator/Suite.php';

include 'core/Repository.php';
include 'core/AST.php';
include 'core/Tokenizer.php';

spl_autoload_register(function($class)
{
	$ar_name = explode('\\',$class);

	if (count($ar_name) !== 2) return true;

	// дозагрузка чекеров, срабатывает когда один чекер наследуется от другого
	if ($ar_name[0] === 'Checkers') {
		include_once 'checkers/' . $ar_name[1] . '.php';
	}

	// загрузка извлекателей
	if ($ar_name[0] === 'Extractors') {
		include_once 'extractors/' . $ar_name[1] . '.php';
	}
});