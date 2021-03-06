<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';
include __DIR__ . '/../../checkers/JsonDecodeArrayCan.php';

class JsonDecodeArrayCan_mock extends \Checkers\JsonDecodeArrayCan {
	public function __construct($source_code)
	{

	}
}

class Checker_JsonDecodeArrayCan extends PHPUnit_Framework_TestCase
{
	private $base_path = 'data/json_decode_array_can/';

	/**
	 * @dataProvider provider_good
	 */
	public function test_JsonDecodeArrayCan_good($code)
	{
		$checker = new JsonDecodeArrayCan_mock('');
		$result = $checker->check(\Tokenizer::get_tokens($code), array());

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
	public function test_JsonDecodeArrayCan_bad($code)
	{
		$checker = new JsonDecodeArrayCan_mock('');
		$result = $checker->check(\Tokenizer::get_tokens($code), array());

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