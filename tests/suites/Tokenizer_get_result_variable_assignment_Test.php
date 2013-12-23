<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';

class Tokenizer_get_result_variable_assignment extends PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider provider
	 */
	public function test_get_result_variable_assignment($tokens, $pos, $var_name)
	{
		$output = \Tokenizer::get_assignment_variable_name($tokens, $pos);

		$this->assertEquals($var_name, $output);
	}

	public function provider()
	{
		$result = array();

		$result[] = array(
			\Tokenizer::get_tokens('<?php $a  = str_replace();'),
			3,
			'$a'
		);

		$result[] = array(
			\Tokenizer::get_tokens('<?php $a->prop = str_replace();'),
			5,
			'$a->prop'
		);

		$result[] = array(
			\Tokenizer::get_tokens('<?php
			$t = 1;
			$a =
			str_replace();'),
			7,
			'$a'
		);

		return $result;
	}
} 