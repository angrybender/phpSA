<?php
namespace Checkers;

class IfConditionIdentical extends \Analisator\ParentChecker
{
	protected $types = array(
		CHECKER_ERRORS
	);

	const
		NODE_NAME = 'PHPParser_Node_Stmt_If';

	protected $error_message = 'Совпадающие условия в управляющем операторе If ... ElseIf ...';

	public function check($nodes)
	{
		$if_nodes = \Core\AST::find_tree_by_root($nodes, self::NODE_NAME);

		foreach ($if_nodes as $if_root) {
			$trees = array();
			if (isset($if_root->elseifs) && count($if_root->elseifs) > 0) {
				$trees[] = $if_root->cond;
				$trees = array_merge($trees, array_map(function($obj) {
					return $obj->cond;
				}, $if_root->elseifs));

				$this->compare($trees);
			}
		}
	}

	/**
	 * сравнивает N переданных нод, являющихся условиями в ветках if блока
	 * @param array $condition_nodes
	 */
	protected function compare(array $condition_nodes)
	{
		foreach ($condition_nodes as $i => $tree_a) {
			foreach ($condition_nodes as $j => $tree_b) {
				if ($i === $j) continue;

				if (\Core\AST::compare_trees(array($tree_a), array($tree_b))) {
					$this->set_error($tree_a->getLine());
					$this->set_error($tree_b->getLine());
				}
			}
		}
	}
}