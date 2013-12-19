<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';

class Procedures_get_all_procedures_in_code extends PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provider
	 */
	public function test_get_all_procedures_in_code($code, $func_list)
	{
		$output = Procedures::get_all_procedures_in_code($code);

		$this->assertEquals($func_list, $output);
	}

	public function provider()
	{
		$cnt = 3;
		$result = array();
		$base_path = 'data/class_procedure/get_all_procedures_in_code/';

		for($i=1; $i<=$cnt; $i++) {
			$result[] = array(
				file_get_contents($base_path . "code.{$i}.txt"),
				include $base_path . "result.{$i}.php"
			);
		}

		return $result;
	}
} 