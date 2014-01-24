<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../CheckerSkeleton.php';

class DateBadOperation_mock extends \Checkers\DateBadOperation {
	public function __construct($source_code)
	{

	}
}

class Checker_DateBadOperation extends CheckerSkeleton
{
	protected $base_path = 'data/checker_date_bad_operation/';
	protected $mock_class_name = 'DateBadOperation_mock';
	protected $extractor = 'Procedure';
}