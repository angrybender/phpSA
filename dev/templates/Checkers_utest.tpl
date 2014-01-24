<?php

include __DIR__ . '/../CheckerSkeleton.php';

class %NAME%_mock extends \Checkers\%NAME% {
	public function __construct($source_code)
	{

	}
}

class Checker_VarReAsset extends CheckerSkeleton
{
	protected $base_path = 'data/%DATA%/';
	protected $mock_class_name = '%NAME%_mock';
	protected $extractor = '';
} 