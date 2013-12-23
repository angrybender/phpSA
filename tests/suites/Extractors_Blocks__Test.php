<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';

class Extractors_Blocks extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider provider
	 */
	public function test_extractors_Blocks($code, $func_list)
	{
		$calle_extractor = new \Extractors\Blocks($code);
		$calle = $calle_extractor->extract(array(
			'block' => 'T_IF'
		));

		//print_r($calle);
		//die();

		$this->assertEquals($func_list, $calle);
	}

	public function provider()
	{
		$cnt = 3;
		$result = array();
		$base_path = 'data/extractors_blocks/';

		for($i=1; $i<=$cnt; $i++) {
			$result[] = array(
				file_get_contents($base_path . "code.{$i}.php"),
				include $base_path . "result.{$i}.php"
			);
		}

		return $result;
	}

	/**
	 * @dataProvider provider_for
	 */
	public function test_extractors_Blocks_for($code, $func_list)
	{
		$calle_extractor = new \Extractors\Blocks($code);
		$calle = $calle_extractor->extract(array(
			'block' => 'T_FOR'
		));

		//print_r($calle);
		//die();

		$this->assertEquals($func_list, $calle);
	}

	public function provider_for()
	{
		$cnt = 4;
		$result = array();
		$base_path = 'data/extractors_blocks/';

		for($i=4; $i<=$cnt; $i++) {
			$result[] = array(
				file_get_contents($base_path . "code.{$i}.php"),
				include $base_path . "result.{$i}.php"
			);
		}

		return $result;
	}
} 