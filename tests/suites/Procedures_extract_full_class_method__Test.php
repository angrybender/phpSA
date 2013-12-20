<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';

class Procedures_extract_full_class_method extends PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provider_extract_full_class_method
	 */
	public function test_extract_full_class_method($code, $result)
	{
		$tokens = Tokenizer::get_tokens('<?php ' . $code);

		$output = Procedures::extract_full_class_method($tokens, 1);

		$this->assertEquals($result, $output);
	}

	public function provider_extract_full_class_method()
	{
		return array(
			array(
				'func()',
				null
			),
			array(
				'func();',
				null
			),
			array(
				'$this->params',
				null
			),
			array(
				'$class->sub()',
				'$class->sub'
			),
			array(
				' $class -> sub (
				)  ',
				'$class->sub'
			),
			array(
				'$class->sub(123)',
				'$class->sub1'
			),
			array(
				'$class->sub($var->ty)->init()',
				'$class->sub($var->ty)->init'
			),
			/*array( // todo
				'$class->sub($class->param())',
				'$class->sub'
			),*/
			array(
				'$class->sub();',
				'$class->sub'
			),
			array(
				'$class->sub(); if { $class2->start() }',
				'$class->sub'
			),
			array(
				'$class->sub()->sub2()',
				'$class->sub()->sub2'
			),
			array(
				'$class->params->sub2()',
				'$class->params->sub2'
			),
			array(
				'fabric()->init->method()',
				'fabric()->init->method'
			)
		);
	}
} 