<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';
include __DIR__ . '/../../checkers/StrReplaceOptimal.php';

class StrReplaceOptimal_mock extends \Checkers\StrReplaceOptimal {
	public function __construct($source_code)
	{

	}
}

class Checker_StrReplaceOptimal  extends PHPUnit_Framework_TestCase
{
	private $base_path = 'data/checker_strreplaceoptimal/';

	/**
	 * @dataProvider provider_good
	 */
	public function test_Checker_StrReplaceOptimal_good($code)
	{
		$checker = new StrReplaceOptimal_mock('');
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
	public function test_Checker_StrReplaceOptimal_bad($code)
	{
		$checker = new StrReplaceOptimal_mock('');
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