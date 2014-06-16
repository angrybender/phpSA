<?php
namespace Checkers;

class LoopIteratorModification extends \Analisator\ParentChecker
{
	protected $types = array(
		CHECKER_ERRORS
	);

	protected $error_message = 'Внутри тела цикла происходит модификация итератора, возможно стоит изменить инструктцию на while';

	public function check($nodes)
	{
		$blocks = \Core\AST::find_tree_by_root($nodes, array(
			'PHPParser_Node_Stmt_For',
		));

		foreach ($blocks as $block) {
			$this->analize($block);
		}
	}

	protected function analize(\PHPParser_Node_Stmt_For $nodes)
	{
		$iterator = $nodes->init[0]->var;
		$assign = \Core\AST::find_tree_by_root($nodes->stmts, array(
			'PHPParser_Node_Expr_Assign',
			'PHPParser_Node_Stmt_Unset',
			'PHPParser_Node_Expr_FuncCall',
		));

		foreach ($assign as $subtree) {
			if ($subtree->getType() === 'Expr_Assign' && \Core\AST::compare_trees(array($iterator), array($subtree->var), true)) {
				$this->set_error($subtree->getLine());
			}
			elseif ($subtree->getType() === 'Stmt_Unset') {
				if ($this->compare_nodes($subtree->vars, $iterator)) {
					$this->set_error($subtree->getLine());
				}
			}
			elseif ($subtree->getType() === 'Expr_FuncCall' && count($subtree->name->parts) === 1) {

				$func_name = $subtree->name->parts[0];
				$args = array();
				if (isset(\Core\Repository::$function_callback_into_variable[$func_name]))
				{
					$args = array_combine(\Core\Repository::$function_callback_into_variable[$func_name], $subtree->args);
				}
				elseif (isset(\Core\Repository::$function_callback_into_variable_infinity[$func_name])) {
					$arg_pos = \Core\Repository::$function_callback_into_variable_infinity[$func_name];
					$args = array_slice($subtree->args, $arg_pos - 1);
				}

				if ($this->compare_nodes($args, $iterator)) {
					$this->set_error($subtree->getLine());
				}
			}
		}
	}

	protected function compare_nodes(array $nodes, $node)
	{
		foreach ($nodes as $var) {

			if ($var->getType() === 'Arg') {
				$var = $var->value;
			}

			if (\Core\AST::compare_trees(array($var), array($node), true)) {
				return true;
			}
		}

		return false;
	}
}