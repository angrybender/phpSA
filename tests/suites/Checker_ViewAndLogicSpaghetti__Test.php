<?php

include __DIR__ . '/../CheckerSkeleton.php';

class ViewAndLogicSpaghetti_mock extends \Checkers\ViewAndLogicSpaghetti {
	public function __construct($source_code)
	{

	}
}

class Checker_VarReAsset extends CheckerSkeleton
{
	protected $base_path = 'data/checker_view_and_logic_spaghetti/';
	protected $mock_class_name = 'ViewAndLogicSpaghetti_mock';
	protected $extractor = 'Procedure';
} 