<?php
namespace Checkers;

class FlowIf extends \Analisator\ParentChecker
{
	protected $types = array(
		CHECKER_ERRORS
	);

	protected $error_message = '';

	public function check($nodes)
	{
		$blocks = \Core\AST::find_tree_by_root($nodes, array(
			'PHPParser_Node_Stmt_ClassMethod',
			'PHPParser_Node_Stmt_Function',
			'PHPParser_Node_Expr_Closure',
		));

		if (empty($blocks)) {
			$blocks = array($nodes);
		}

		foreach ($blocks as $unit) {
			$flow = new \Core\Flow\FlowIf($unit);
			$errors = $flow->getErrors();
			$errors = array_map(function($value) {
				$value['message'] = "Выражение {$value['message'][1]} входит в противоречие с выражением {$value['message'][0]}";
				return $value;
			}, $errors);
			$this->custom_errors = array_merge($this->custom_errors, $errors);
		}
	}
}