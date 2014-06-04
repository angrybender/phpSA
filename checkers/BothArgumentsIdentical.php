<?php
namespace Checkers;

/**
 * ловит ошибки вроде $a && $a
 * Class BothArgumentsIdentical
 * @package Checkers
 */
class BothArgumentsIdentical extends \Analisator\ParentChecker
{
	protected $types = array(
		CHECKER_ERRORS
	);

	protected $error_message = 'Идентичные операнды';

	/**
	 * какие операторы необходимо проверять и какие у них суб деревья (по умочанию - left/right)
	 * @var array
	 */
	private $operators = array(
		'PHPParser_Node_Expr_Assign' => array('var', 'expr'),
		'PHPParser_Node_Expr_BitwiseAnd' => null,
		'PHPParser_Node_Expr_BitwiseNot' => null,
		'PHPParser_Node_Expr_BitwiseOr' => null,
		'PHPParser_Node_Expr_BitwiseXor' => null,
		'PHPParser_Node_Expr_BooleanAnd' => null,
		'PHPParser_Node_Expr_BooleanOr' => null,
		'PHPParser_Node_Expr_Div' => null,
		'PHPParser_Node_Expr_Equal' => null,
		'PHPParser_Node_Expr_Greater' => null,
		'PHPParser_Node_Expr_GreaterOrEqual' => null,
		'PHPParser_Node_Expr_Identical' => null,
		'PHPParser_Node_Expr_LogicalAnd' => null,
		'PHPParser_Node_Expr_LogicalOr' => null,
		'PHPParser_Node_Expr_LogicalXor' => null,
		'PHPParser_Node_Expr_Minus' => null,
		'PHPParser_Node_Expr_Mod' => null,
		'PHPParser_Node_Expr_NotEqual' => null,
		'PHPParser_Node_Expr_NotIdentical' => null,
		'PHPParser_Node_Expr_Plus' => null,
		'PHPParser_Node_Expr_ShiftLeft' => null,
		'PHPParser_Node_Expr_ShiftRight' => null,
		'PHPParser_Node_Expr_Smaller' => null,
		'PHPParser_Node_Expr_SmallerOrEqual' => null,
	);

	public function check($nodes)
	{
		foreach ($this->operators as $class_name => $operand_nodes_name) {
			if ($operand_nodes_name === null) {
				$operand_nodes_name = array('left', 'right');
			}
			$found = \Core\AST::find_tree_by_root($nodes, $class_name);

			foreach ($found as $tree) {
				$this->operator_check($tree->{$operand_nodes_name[0]}, $tree->{$operand_nodes_name[1]});
			}
		}
	}

	protected function operator_check($tree_a, $tree_b)
	{
		if (\Core\AST::compare_trees(array($tree_a), array($tree_b))) {
			$this->set_error(\Core\AST::get_line_of_tree($tree_a));
		}
	}
}