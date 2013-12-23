<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';

class Tokenizer_token_find extends PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider provider
	 */
	public function test_token_find($tokens, $needle, $pos)
	{
		$output = \Tokenizer::token_find($tokens, $needle);

		$this->assertEquals($pos, $output);
	}

	public function provider()
	{
		$result = array();

		$result[] = array(
			\Tokenizer::get_tokens('<?php $a  = str_replace();'),
			\Tokenizer::get_tokens_of_expression('str_replace();'),
			3
		);

		$result[] = array(
			\Tokenizer::get_tokens('<?php
			$t = 1;
			$a->item =
			str_replace();'),
			\Tokenizer::get_tokens_of_expression('str_replace'),
			9
		);

		return $result;
	}
} 