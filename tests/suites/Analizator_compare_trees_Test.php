<?php
/**
 *
 * @author k.vagin
 */
include __DIR__ . '/../../bootstrap.php';

class Analizator_compare_trees extends PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider provider_identical
	 */
	public function test_identical($a, $b)
	{
		$a = \Core\Tokenizer::parser(file_get_contents($a));
		$b = \Core\Tokenizer::parser(file_get_contents($b));
		$this->assertEquals(true, \Core\AST::compare_trees($a, $b));
	}

	public function provider_identical()
	{
		$base_path = __DIR__ . "/../data/analizator_compare_trees/ident/";
		$files = array();
		$cnt = count(glob("{$base_path}a*.php"));

		for ($i = 1; $i <= $cnt; $i++) {
			$files[] = array(
				$base_path . "a.{$i}.php",
				$base_path . "b.{$i}.php",
			);
		}

		return $files;
	}
} 