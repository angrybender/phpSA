<?php
/**
 * todo пока нет алгоритма
 * @author k.vagin
 */
include __DIR__ . '/../../bootstrap.php';

class Analizator_get_similar_trees extends PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider provider_identical
	 */
	public function test_run($a, $similar_trees)
	{
		$a = \Core\Tokenizer::parser(file_get_contents($a));
		//$b = \Core\Tokenizer::parser(file_get_contents($b));
		$this->assertEquals(\Core\AST::get_similar_trees($a[0]->expr), true);
	}

	public function provider_identical()
	{
		$base_path = __DIR__ . "/../data/analizator_get_similar_trees/ident/";
		$files = array();
		$cnt = count(glob("{$base_path}a*.php"));

		for ($i = 1; $i <= $cnt; $i++) {
			$files[] = array(
				$base_path . "a.{$i}.php",
				glob("{$base_path}b.{$i}*.php")
			);
		}

		return $files;
	}
} 