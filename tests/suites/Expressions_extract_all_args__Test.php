<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';

class Expressions_extract_all_args extends PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provider
	 */
	public function test_rn($expression, $result)
	{
		$expression = \Tokenizer::get_tokens_of_expression($expression);
		$expression = \Expressions::extract_all_args($expression);
		$expression = join(', ', $expression);
		$this->assertEquals($result,  $expression);
	}

	public function provider()
	{
		return array(
			array(
				'$b',
				'$b'
			),
			array(
				'$aar->prop, 1, $t',
				'$aar->prop, 1, $t'
			),
			array(
				'func($a, $b), 1, $t',
				'func($a,$b), 1, $t'
			),
			array(
				'func	($a, $b->prop($c)
				), 1, $t',

				'func($a,$b->prop($c)), 1, $t'
			),
		);
	}
} 