<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';

class Heuristic_mock extends \Heuristic {
	public static function wrap_one_line_blocks_pub($tokens)
	{
		return self::wrap_one_line_blocks($tokens);
	}
}

class Heuristic_wrap_one_line extends PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider provider
	 */
	public function test($in, $out)
	{
		$result = Heuristic_mock::wrap_one_line_blocks_pub(\Tokenizer::get_tokens_of_expression($in));
		$this->assertEquals($out, \Tokenizer::tokens_to_source($result));
	}

	public function provider()
	{
		return array(
			array(
				'
					if (true) echo "a";
				',
				'if(true){echo"a";}'
			),
			array(
				'
					if (($==1) || ($b==2)) echo "a";
				',
				'if(($==1)||($b==2)){echo"a";}'
			),
			array(
				'
					if (true)
						if (false) echo "b";
				',
				'if(true){if(false){echo"b";}}'
			),
		);
	}
} 