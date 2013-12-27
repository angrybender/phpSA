<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';

class Heuristic_similar extends PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider provider
	 */
	public function test($code1, $code1a, $is_similar)
	{
		$code1 = \Tokenizer::get_tokens($code1);
		$code1a = \Tokenizer::get_tokens($code1a);

		$s_1a = \CopyPaste::code_simplification($code1a);
		$s_1 = \CopyPaste::code_simplification($code1);

		//echo $s_1, PHP_EOL;
		//echo $s_1a, PHP_EOL;

		if ($is_similar) {
			$this->assertEquals($s_1, $s_1a);
		}
		else {
			$this->assertNotEquals($s_1, $s_1a);
		}
	}

	public function provider()
	{
		return array(
			array(
				file_get_contents('data/code_similar/1.php'),
				file_get_contents('data/code_similar/1a.php'),
				true
			),
		);
	}
} 