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

	protected $error_message = 'Переобозначение переменной без использования';

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
		$suspicious_operand = array();

		// unset считаем за присваивание
		$suspicious_expr = \Core\AST::find_tree_by_root($nodes, 'PHPParser_Node_Stmt_Unset', false);
		foreach ($suspicious_expr as $expr) {
			$suspicious_operand = array_merge($suspicious_operand, $expr->vars);
		}

		$suspicious_expr = \Core\AST::find_tree_by_root($nodes, 'PHPParser_Node_Expr_Assign', false);
		foreach ($suspicious_expr as $expr) {

			// первоначальная инициализация всякими false, array() и тд

			if ($expr->expr->getType() === 'Expr_ConstFetch'
				|| $expr->expr->getType() === 'Scalar_LNumber'
				|| $expr->expr->getType() === 'Scalar_String'
			) {
				continue;
			}
			if ($expr->expr->getType() === 'Expr_Array' && empty($expr->expr->items)) {
				continue;
			}

			if ($expr->var->getType() === 'Expr_ArrayDimFetch' && $expr->var->dim === null) {
				$where = $this->in_array_find($suspicious_operand, $expr->var, false);
				$suspicious_operand[$where] = $expr->var->var; // подменяем последнее вхождение чтобы ловить ситуацию из /tests/data/checker_var_re_asset/bad/array.php
				continue;
			}

			if (($prev_line = $this->in_array_find($suspicious_operand, $expr->var))
				&& $this->check_behavior($expr, $nodes, $prev_line)
				&& $prev_line < $expr->getLine()) {
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
		if (is_object($nodes) && in_array('stmts', $nodes->getSubNodeNames())) {
			$nodes = $nodes->stmts;
		}
		foreach ($nodes as $node) {
			if (!is_object($node)) {
				continue;
			}

			if ($node->getLine() <= $prev_line || $node->getLine() >= $expression->getLine()) {
				continue;
			}

			// ищет операнд как аргумент вызова функции:

			if (count(\Core\AST::find_subtrees($node, new \PHPParser_Node_Arg($expression->var, false), true)) > 0) {
				return false;
			}

			if (count(\Core\AST::find_subtrees($node, new \PHPParser_Node_Arg($expression->var, true), true)) > 0) {
				return false;
			}

			// ищет как вхождение в условие
			if (in_array('cond', $node->getSubNodeNames()) && count(\Core\AST::find_subtrees($node->cond, $expression->var, true)) > 0 ) {
				return false;
			}

			// ищет в присваивании
			if ($node->getType() === 'Expr_Assign' && count(\Core\AST::find_subtrees($node->expr, $expression->var, true)) > 0 ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param array $arr
	 * @param \PHPParser_Node $tree
	 * @param bool $return_code_line
	 * @return bool
	 */
	protected function in_array_find(array $arr, $tree, $return_code_line = true)
	{
		foreach ($arr as $i => $node)
		{
			if (\Core\AST::compare_trees(array($node), array($tree))) {
				return  $return_code_line ? $node->getLine() : $i;
			}
		}

		return false;
	}
} 