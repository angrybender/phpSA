<?php
/**
 *
 * @author k.vagin
 */

namespace Core\Flow\Trace\Operators\Blocks;


class Stmt_For extends \Core\Flow\Trace\Operators\AOperator
{
	public function result()
	{
		foreach (array('init', 'loop', 'cond', 'stmts') as $sub_node) {
			$this->deep($this->node->{$sub_node});
		}
	}

	private function deep(array $exprs)
	{
		foreach ($exprs as $expr) {
			$this->toTrace($expr);
		}
	}
} 