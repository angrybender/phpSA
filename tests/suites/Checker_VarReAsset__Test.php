<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';

include __DIR__ . '/../../checkers/VarUndefined.php';
include __DIR__ . '/../../checkers/VarReAsset.php';

class VarReAsset_mock extends \Checkers\VarReAsset {
	public function __construct($source_code)
	{

	}
}

class Checker_VarReAsset extends PHPUnit_Framework_TestCase
{
	private $base_path = 'data/checker_var_re_asset/';

	/**
	 * @dataProvider provider_good
	 */
	public function test_ConditionsOptimal_good($code)
	{
		$checker = new VarReAsset_mock('');
		$result = $checker->check(\Tokenizer::get_tokens($code));

		$this->assertEquals(true, $result);
	}

	public function provider_good()
	{
		$files = scandir($this->base_path.'good/');
		$result = array();
		foreach ($files as $file) {
			if ($file !== '.' && $file !== '..') {
				$result[] = array(file_get_contents($this->base_path . 'good/' . $file));
			}
		}

		return $result;
	}


	/**
	 * @dataProvider provider_bag
	 */
	public function test_ConditionsOptimal_bad($code)
	{
		$checker = new VarReAsset_mock('');
		$result = $checker->check(\Tokenizer::get_tokens($code));

		$this->assertEquals(false, $result);
	}

	public function provider_bag()
	{
		$files = scandir($this->base_path . 'bad/');
		$result = array();
		foreach ($files as $file) {
			if ($file !== '.' && $file !== '..') {
				$result[] = array(file_get_contents($this->base_path . 'bad/' . $file));
			}
		}

		return $result;
	}
} 