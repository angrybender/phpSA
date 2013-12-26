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

class Checker_TypeConversionWithoutAssets extends CheckerSkeleton
{
	protected $base_path = 'data/checker_pre_defined_in_sql_query/';

	/**
	 * @dataProvider provider_good
	 */
	public function test_ConditionsOptimal_good($code)
	{
		$checker = new PreDefinedInSqlQuery_mock('');
		$result = $checker->check(\Tokenizer::get_tokens($code));

		$this->assertEquals(true, $result);
	}

	/**
	 * @dataProvider provider_bag
	 */
	public function test_ConditionsOptimal_bad($code)
	{
		$checker = new PreDefinedInSqlQuery_mock('');
		$result = $checker->check(\Tokenizer::get_tokens($code));

		$this->assertEquals(false, $result);
	}
} 