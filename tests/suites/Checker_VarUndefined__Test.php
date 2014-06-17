<?php
/**
 *
 * @author k.vagin
 */


include __DIR__ . '/../CheckerSkeleton.php';

include __DIR__ . '/../../workers/ClassInformation.php';
include __DIR__ . '/../../hooks/IndexerClassInformation.php';

$worker = new \Workers\ClassInformation();
$worker->class_info = array(array(
	'name' => 'MyClass',
	'methods' => array(
		array(
			'name' => 'function_with_ref_call',
			'args' => array('BY_LINK')
		),
		array(
			'name' => 'checkBodyForDictionaryHeader',
			'args' => array('BY_VAL', 'BY_LINK', 'BY_LINK')
		)
	),
	'properties' => array('$_end')
));

$hook = new \Hooks\IndexerClassInformation();
$hook->run();

class Checker_VarUndefined extends CheckerSkeleton
{
	protected $base_path = 'data/checker_var_undefined/';
	protected $class_name = 'VarUndefined';
} 