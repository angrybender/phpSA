<?php
/**
 *
 * @author k.vagin
 */
include __DIR__ . '/../../bootstrap.php';

class AST_swipe_nodes_Test extends PHPUnit_Framework_TestCase
{
	public function test_swipe()
	{
		$source = \Core\Tokenizer::parser('<?php $a && $b && $c;');
		$source = $source[0];


		$result = \Core\AST::swipe_nodes($source->left, $source->right, 'left', null);
		$code = \Core\Tokenizer::printer(new \PHPParser_Node_Expr_BooleanAnd($result[0], $result[1]));
		$this->assertEquals('$c && $b && $a', $code);


		$result = \Core\AST::swipe_nodes($source->left, $source->right, 'right', null);
		$code = \Core\Tokenizer::printer(new \PHPParser_Node_Expr_BooleanAnd($result[0], $result[1]));
		$this->assertEquals('$a && $c && $b', $code);



		$source = \Core\Tokenizer::parser('<?php $a && $b || $c && $d;');
		$source = $source[0];


		$result = \Core\AST::swipe_nodes($source->left, $source->right, 'left', 'left');
		$code = \Core\Tokenizer::printer(new \PHPParser_Node_Expr_BooleanOr($result[0], $result[1]));
		$this->assertEquals('$c && $b || $a && $d', $code);


		$result = \Core\AST::swipe_nodes($source->left, $source->right, 'right', 'left');
		$code = \Core\Tokenizer::printer(new \PHPParser_Node_Expr_BooleanOr($result[0], $result[1]));
		$this->assertEquals('$a && $c || $b && $d', $code);


		$result = \Core\AST::swipe_nodes($source->left, $source->right, 'right', 'right');
		$code = \Core\Tokenizer::printer(new \PHPParser_Node_Expr_BooleanOr($result[0], $result[1]));
		$this->assertEquals('$a && $d || $c && $b', $code);


		$result = \Core\AST::swipe_nodes($source->left, $source->right, 'right', null);
		$code = \Core\Tokenizer::printer(new \PHPParser_Node_Expr_BooleanOr($result[0], $result[1]));
		$this->assertEquals('$a && ($c && $d) || $b', $code);
	}
} 