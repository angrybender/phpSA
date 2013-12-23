<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';

class Extractors_CalledFunction extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider provider
	 */
	public function test_extractors_calledfunction($code, $func_list)
	{
		$calle_extractor = new \Extractors\CalledFunction($code);
		$calle = $calle_extractor->extract(array(
			'name' => 'str_replace'
		));

		$this->assertEquals($func_list, $calle);
	}

	public function provider()
	{
		$cnt = 2;
		$result = array();
		$base_path = 'data/extractors_called_function/';

		for($i=1; $i<=$cnt; $i++) {
			$result[] = array(
				file_get_contents($base_path . "code.{$i}.php"),
				include $base_path . "result.{$i}.php"
			);
		}

		return $result;
	}
} 