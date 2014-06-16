<?php
/**
 * переобозначение переменной
 * 	$var = 1234;
	..
	..
	$var = 456; // зачем дважды?
 *
 *
 * @author k.vagin
 */

namespace Checkers;


class VarReAsset extends \Analisator\ParentChecker
{
	protected $types = array(
		CHECKER_HEURISTIC
	);

	protected $error_message = 'Переменная переобозначается ниже, а до этого не используется';

	public function check($nodes)
	{
		$blocks = \Core\AST::find_tree_by_root($nodes, array(
			'PHPParser_Node_Stmt_ClassMethod',
			'PHPParser_Node_Stmt_Function',
			'PHPParser_Node_Expr_Closure',
			'PHPParser_Node_Stmt_If',
			'PHPParser_Node_Stmt_For',
			'PHPParser_Node_Stmt_Foreach',
			'PHPParser_Node_Stmt_While',
		));

		if (empty($blocks)) {
			$blocks = array($nodes);
		}

		foreach ($blocks as $nodes) {
			$this->analize_sub_code($nodes);
		}
	}

	/**
	 * @param \PHPParser_Node[]|\PHPParser_Node $nodes
	 */
	protected function analize_sub_code($nodes)
	{
		$suspicious_expr = \Core\AST::find_tree_by_root($nodes, 'PHPParser_Node_Expr_Assign', false);
		$suspicious_operand = array();
		foreach ($suspicious_expr as $expr) {
			if (($prev_line = $this->in_array_find($suspicious_operand, $expr->var)) && $this->check_behavior($expr, $nodes, $prev_line)) {
				$this->set_error($expr->getLine());
			}
			else {
				$suspicious_operand[] = $expr->var;
			}
		}
	}

	/**
	 * проверяем нет ли тут неопределенного поведения и ложного срабатывания
	 * @param \PHPParser_Node_Expr_Assign $expression
	 * @param \PHPParser_Node[]|\PHPParser_Node $nodes
	 * @param int $prev_line
	 * @return int
	 */
	protected function check_behavior(\PHPParser_Node_Expr_Assign $expression, $nodes, $prev_line = 0)
	{
		// проверка на вхождение в массивы и тд
		if (count(\Core\AST::find_subtrees($expression->expr, $expression->var, true)) > 0) {
			return false;
		}

		// проверка на использование между присваиваниями
		foreach ($nodes as $node) {
			if (!is_object($node)) {
				continue;
			}

			if ($node->getLine() <= $prev_line || $node->getLine() >= $expression->getLine()) {
				continue;
			}

			if (count(\Core\AST::find_subtrees($node, $expression->var, true)) > 0) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param @param \PHPParser_Node[] $arr
	 * @param \PHPParser_Node $tree
	 * @return bool
	 */
	protected function in_array_find(array $arr, $tree)
	{
		foreach ($arr as $node)
		{
			if (\Core\AST::compare_trees(array($node), array($tree))) {
				return $node->getLine();
			}
		}

		return false;
	}
} 