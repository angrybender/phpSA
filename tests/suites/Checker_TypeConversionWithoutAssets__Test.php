<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../CheckerSkeleton.php';

class TypeConversionWithoutAssets_mock extends \Checkers\TypeConversionWithoutAssets {
	public function __construct($source_code)
	{

	}
}

class Checker_TypeConversionWithoutAssets extends CheckerSkeleton
{
	protected $base_path = 'data/checker_type_conversion_without_assets/';

	/**
	 * @dataProvider provider_good
	 */
	public function test_ConditionsOptimal_good($code)
	{
		$checker = new TypeConversionWithoutAssets_mock('');
		$result = $checker->check(\Tokenizer::get_tokens($code));

		$this->assertEquals(true, $result);
	}

	/**
	 * @dataProvider provider_bag
	 */
	public function test_ConditionsOptimal_bad($code)
	{
		$checker = new TypeConversionWithoutAssets_mock('');
		$result = $checker->check(\Tokenizer::get_tokens($code));

		$this->assertEquals(false, $result);
	}
} 