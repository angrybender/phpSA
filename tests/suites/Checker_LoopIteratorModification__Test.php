<?php

include __DIR__ . '/../CheckerSkeleton.php';

class LoopIteratorModification_mock extends \Checkers\LoopIteratorModification {
	public function __construct($source_code)
	{

	}
}

class Checker_VarReAsset extends CheckerSkeleton
{
	protected $base_path = 'data/checker_loop_iterator_modification/';
	protected $mock_class_name = 'LoopIteratorModification_mock';
	protected $extractor = 'BlocksWithHead';
	protected $filter = array(
		'block' => 'T_FOR'
	); //фильтр для извлекателя
} 