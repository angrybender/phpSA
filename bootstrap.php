<?php
/**
 * @author k.vagin
 */

ini_set('memory_limit', '2048M');

include 'third_party/php_parser/lib/bootstrap.php';

include 'analisator/def.types_of_checkers.php';

include 'analisator/ParentChecker.php';
include 'analisator/ParentExtractor.php';
include 'analisator/ParentWorker.php';
include 'analisator/ParentHook.php';
include 'analisator/ParentInit.php';

include 'analisator/Report.php';
include 'analisator/Config.php';
include 'analisator/Suite.php';

include 'core/Repository.php';
include 'core/Tokenizer.php';
include 'core/AST.php';

include 'core/flow/VarTypes.php';
include 'core/flow/FlowIf.php';
include 'core/flow/FlowVar.php';
include 'core/flow/Solver.php';
include 'core/flow/ExprFalse.php';
include 'core/flow/ExprTrue.php';
include 'core/flow/ExprEq.php';

include 'core/flow/procedure/ReturnType.php';

spl_autoload_register(function($class)
{
	$ar_name = explode('\\',$class);

	if (count($ar_name) < 2) return true;

	// дозагрузка чекеров, срабатывает когда один чекер наследуется от другого
	if ($ar_name[0] === 'Checkers') {
		include_once 'checkers/' . $ar_name[1] . '.php';
	}

	// загрузка извлекателей
	if ($ar_name[0] === 'Extractors') {
		include_once 'extractors/' . $ar_name[1] . '.php';
	}

	// "трассировка кода"
	if (count($ar_name) > 3 && $ar_name[0] === 'Core' && $ar_name[1] === 'Flow' && $ar_name[2] === 'Trace') {
		$class_name = array_pop($ar_name);
		$path = strtolower(join(DIRECTORY_SEPARATOR, $ar_name)) . DIRECTORY_SEPARATOR . $class_name . '.php';
		include_once $path;
	}
});