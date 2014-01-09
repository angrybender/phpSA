<?php
/**
 *
 * @author k.vagin
 */


include __DIR__ . '/../CheckerSkeleton.php';

include __DIR__ . '/../../workers/ClassInformation.php';
include __DIR__ . '/../../hooks/IndexerClassInformation.php';

$worker = new \Workers\ClassInformation();
$worker->class_and_his_methods = array(array(
	'name' => 'MyClass',
	'methods' => array(
		array(
			'name' => 'function_with_ref_call',
			'args' => array('BY_LINK')
		),
	))
);

$hook = new \Hooks\IndexerClassInformation();
$hook->run();

class VarUndefined_mock extends \Checkers\VarUndefined {
	public function __construct($source_code)
	{

	}
}

class Checker_VarUndefined extends CheckerSkeleton
{
	protected $base_path = 'data/checker_var_undefined/';
	protected $mock_class_name = 'VarUndefined_mock';
	protected $is_need_token_convert = true;
} 