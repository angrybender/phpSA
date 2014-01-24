<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';
include __DIR__ . '/../../checkers/ConditionsOptimal.php';

class ConditionsOptimal_mock extends \Checkers\ConditionsOptimal {
	public function __construct($source_code)
	{

	}
}

class Checker_ConditionsOptimal extends PHPUnit_Framework_TestCase
{
	private $base_path = 'data/checker_conditions_optimal/';

	/**
	 * @dataProvider provider_good
	 */
	public function test_ConditionsOptimal_good($code)
	{
		$checker = new ConditionsOptimal_mock('');
		$result = $checker->check($code, array());

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
		$checker = new ConditionsOptimal_mock('');
		$result = $checker->check($code, array());

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