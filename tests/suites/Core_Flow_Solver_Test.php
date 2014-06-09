<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';

class Core_Flow_Solver_Test extends PHPUnit_Framework_TestCase
{
	public function test_base()
	{
		$code = '<?php (!($a || $b)) && ($a && $b);';
		$nodes = \Core\Tokenizer::parser($code);

		$result = \Core\Flow\Solver::boolean_tree_optimize($nodes[0]);

		$this->assertEquals('((!$a && !$b) && ($a && $b))', $result);
	}

	public function test_2()
	{
		$code = '<?php (!($a && $b)) && ($a || $b);';
		$nodes = \Core\Tokenizer::parser($code);

		$result = \Core\Flow\Solver::boolean_tree_optimize($nodes[0]);

		$this->assertEquals('((!$a || !$b) && ($a || $b))', $result);
	}

	public function test_3()
	{
		$code = '<?php (!($this->a($c || 1)->b || $b)) && ($a || $b);';
		$nodes = \Core\Tokenizer::parser($code);

		$result = \Core\Flow\Solver::boolean_tree_optimize($nodes[0]);

		$this->assertEquals('((!$this->a($c || 1)->b && !$b) && ($a || $b))', $result);
	}

	public function test_4()
	{
		$code = '<?php (!($a && !$b)) && ($a || $b);';
		$nodes = \Core\Tokenizer::parser($code);

		$result = \Core\Flow\Solver::boolean_tree_optimize($nodes[0]);

		$this->assertEquals('((!$a || $b) && ($a || $b))', $result);
	}

	public function test_5()
	{
		$code = '<?php (($a || $b) && !($c && $d));';
		$nodes = \Core\Tokenizer::parser($code);

		$result = \Core\Flow\Solver::boolean_tree_optimize($nodes[0]);

		$this->assertEquals('(($a || $b) && (!$c || !$d))', $result);
	}

	public function test_mix_1()
	{
		$code = '<?php ($a == 1 || $b);';
		$nodes = \Core\Tokenizer::parser($code);

		$result = \Core\Flow\Solver::boolean_tree_optimize($nodes[0]);

		$this->assertEquals('($a == 1 || $b)', $result);
	}

	public function test_mix_2()
	{
		$code = '<?php (!(1 + $a > $c) || $d);';
		$nodes = \Core\Tokenizer::parser($code);

		$result = \Core\Flow\Solver::boolean_tree_optimize($nodes[0]);

		$this->assertEquals('(1 + $a <= $c || $d)', $result);
	}

	public function test_mix_3()
	{
		$code = '<?php (!$a == $b) && !($a == $d);';
		$nodes = \Core\Tokenizer::parser($code);

		$result = \Core\Flow\Solver::boolean_tree_optimize($nodes[0]);

		$this->assertEquals('(!$a == $b && $a != $d)', $result);
	}

	/**
	 * @expectedException \Core\Flow\Exceptions\ExprFalse
	 */
	public function test_estimate_on_boolean_collision()
	{
		$code = '<?php ($a && $b && !$a);';
		$nodes = \Core\Tokenizer::parser($code);

		\Core\Flow\Solver::estimate_on_boolean_collision($nodes[0]);
	}

	/**
	 * ok
	 */
	public function test_estimate_on_boolean_collision2()
	{
		$code = '<?php ($a && $b || !$a);';
		$nodes = \Core\Tokenizer::parser($code);

		\Core\Flow\Solver::estimate_on_boolean_collision($nodes[0]);

		$code = '<?php (!$a == $b && $a != $d);';
		$nodes = \Core\Tokenizer::parser($code);

		\Core\Flow\Solver::estimate_on_boolean_collision($nodes[0]);

		$code = '<?php ($a <> $b || $a <> 12);';
		$nodes = \Core\Tokenizer::parser($code);

		\Core\Flow\Solver::estimate_on_boolean_collision($nodes[0]);

		$code = '<?php ($a > $b && $a > 12);';
		$nodes = \Core\Tokenizer::parser($code);

		\Core\Flow\Solver::estimate_on_boolean_collision($nodes[0]);
	}

	/**
	 * @expectedException \Core\Flow\Exceptions\ExprTrue
	 */
	public function test_estimate_on_boolean_collision3()
	{
		$code = '<?php ($a || $b || !$a);';
		$nodes = \Core\Tokenizer::parser($code);

		\Core\Flow\Solver::estimate_on_boolean_collision($nodes[0]);
	}

	/**
	 * @expectedException \Core\Flow\Exceptions\ExprEq
	 */
	public function test_estimate_on_boolean_collision4_eq()
	{
		$code = '<?php ($a == $b && $a == 12);';
		$nodes = \Core\Tokenizer::parser($code);

		\Core\Flow\Solver::estimate_on_boolean_collision($nodes[0]);
	}

	/**
	 * @expectedException \Core\Flow\Exceptions\ExprEq
	 */
	public function test_estimate_on_boolean_collision6_eq()
	{
		$code = '<?php ($a == $b && 12 == $a);';
		$nodes = \Core\Tokenizer::parser($code);

		\Core\Flow\Solver::estimate_on_boolean_collision($nodes[0]);
	}

	/**
	 * @expectedException \Core\Flow\Exceptions\ExprEq
	 */
	public function test_estimate_on_boolean_collision5_eq()
	{
		$code = '<?php ($a <> $b && $a <> 12);';
		$nodes = \Core\Tokenizer::parser($code);

		\Core\Flow\Solver::estimate_on_boolean_collision($nodes[0]);
	}
}