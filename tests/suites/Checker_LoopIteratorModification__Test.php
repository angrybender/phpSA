<?php

include __DIR__ . '/../CheckerSkeleton.php';

\Core\Repository::$function_callback_into_variable = array(
	'pcntl_wait' => array(1),
);
\Core\Repository::$function_callback_into_variable_infinity = array(
	'sscanf' => 3,
);

class Checker_VarReAsset extends CheckerSkeleton
{
	protected $base_path = 'data/checker_loop_iterator_modification/';
	protected $class_name = 'LoopIteratorModification';
} 