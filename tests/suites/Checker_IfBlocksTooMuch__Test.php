<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../CheckerSkeleton.php';

class IfBlocksTooMuch_mock extends \Checkers\IfBlocksTooMuch {
	public function __construct($source_code)
	{

	}
}

class Checker_IfBlocksTooMuch  extends CheckerSkeleton
{
	protected $base_path = 'data/checker_if_blocks_too_much/';
	protected $mock_class_name = 'IfBlocksTooMuch_mock';
	protected $extractor = 'Blocks';
	protected $filter = array(
		'block' => 'T_IF'
	);
} 