<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';

class Heuristic_mock extends \CopyPaste {
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
					if ((int)true) echo "a";
				',
				'if((int)true){echo"a";}'
			),
			array(
				'
					if (true) echo "a"; else echo "b";
				',
				'if(true){echo"a";}else{echo"b";}'
			),
			array(
				'
					if (true)
						echo "a";
					else {
						echo "b";
					}
				',
				'if(true){echo"a";}else{echo"b";}'
			),
			array(
				'
					if (true)
						echo "a";
					elseif (false)
						echo "b";
				',
				'if(true){echo"a";}elseif(false){echo"b";}'
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
			array(
				'if(true){if(false){echo"b";}}',
				'if(true){if(false){echo"b";}}'
			)
		);
	}
} 