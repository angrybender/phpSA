<?php
/**
 *
 * @author k.vagin
 */
include __DIR__ . '/../../bootstrap.php';

class AST_tree_sort extends PHPUnit_Framework_TestCase
{
	public function test_sort1()
	{
		$source = \Core\Tokenizer::parser('<?php $c && $b && $a;');
		$source = $source[0];

		$result = \Core\AST::tree_sort($source);
		$code = \Core\Tokenizer::printer($result);

		$this->assertEquals('$a && $b && $c', $code);
	}

	public function test_sort2()
	{
		$source = \Core\Tokenizer::parser('<?php $a && $c && $b;');
		$source = $source[0];

		$result = \Core\AST::tree_sort($source);
		$code = \Core\Tokenizer::printer($result);

		$this->assertEquals('$a && $b && $c', $code);
	}

	public function test_sort3()
	{
		$source = \Core\Tokenizer::parser('<?php $a || $c && $b;');
		$source = $source[0];

		$result = \Core\AST::tree_sort($source);
		$code = \Core\Tokenizer::printer($result);

		$this->assertEquals('$a || $b && $c', $code);
	}

	public function test_sort4()
	{
		$source = \Core\Tokenizer::parser('<?php $b && $c && $d && $a;');
		$source = $source[0];

		$result = \Core\AST::tree_sort($source);
		$code = \Core\Tokenizer::printer($result);

		$this->assertEquals('$a && $b && $c && $d', $code);
	}

	public function test_sort5()
	{
		$source = \Core\Tokenizer::parser('<?php $this->method($c || $a || $b);');
		$source = $source[0];

		$result = \Core\AST::tree_sort($source);
		$code = \Core\Tokenizer::printer($result);

		$this->assertEquals('$this->method($a || $b || $c)', $code);
	}

	public function test_sort6()
	{
		$source = \Core\Tokenizer::parser('<?php is_array($arr) && $this->method($c || $a || $b);');
		$source = $source[0];

		$result = \Core\AST::tree_sort($source);
		$code = \Core\Tokenizer::printer($result);

		$this->assertEquals('$this->method($a || $b || $c) && is_array($arr)', $code);
	}
}