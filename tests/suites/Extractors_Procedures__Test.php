<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';

class Extractors_Procedures extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider provider
	 */
	public function test_extractors($code, $func_list)
	{
		$calle_extractor = new \Extractors\Procedure($code);
		$calle = $calle_extractor->extract();

		//print_r($calle);
		//die();

		$result = array();
		foreach ($calle as $func) {
			$result[] = array(
				'name' => $func['name']
			);
		}

		$this->assertEquals($func_list, $result);
	}

	public function provider()
	{
		$cnt = 3;
		$result = array();
		$base_path = 'data/extractors_procedure/';

		for($i=1; $i<=$cnt; $i++) {
			$result[] = array(
				file_get_contents($base_path . "{$i}.php"),
				include $base_path . "result.{$i}.php"
			);
		}

		return $result;
	}
} 