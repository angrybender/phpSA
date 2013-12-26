<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../CheckerSkeleton.php';

class PreDefinedInSqlQuery_mock extends \Checkers\PreDefinedInSqlQuery {
	public function __construct($source_code)
	{

	}
}

class Checker_PreDefinedInSqlQuery extends CheckerSkeleton
{
	protected $base_path = 'data/checker_pre_defined_in_sql_query/';
	protected $mock_class_name = 'PreDefinedInSqlQuery_mock';
	protected $is_need_token_convert = true;

} 