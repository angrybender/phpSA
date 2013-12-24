<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';
include __DIR__ . '/../../checkers/VarUndefined.php';

class VarUndefined_mock extends \Checkers\VarUndefined {
	public function __construct($source_code)
	{

	}
}

class Checker_VarUndefined extends PHPUnit_Framework_TestCase
{
	private $base_path = 'data/checker_var_undefined/';

	/**
	 * @dataProvider provider_good
	 */
	public function test_good($code)
	{
		$checker = new VarUndefined_mock('');
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
	public function test_bad($code)
	{
		$checker = new VarUndefined_mock('');
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