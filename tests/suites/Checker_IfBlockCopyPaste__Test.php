<?php

include __DIR__ . '/../CheckerSkeleton.php';

class IfBlockCopyPaste_mock extends \Checkers\IfBlockCopyPaste {
	public function __construct($source_code)
	{

	}
}

class Checker_VarReAsset extends CheckerSkeleton
{
	protected $base_path = 'data/checker_if_block_copy_paste/';
	protected $mock_class_name = 'IfBlockCopyPaste_mock';
	protected $extractor = 'Procedure';
} 