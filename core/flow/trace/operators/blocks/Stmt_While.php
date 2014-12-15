<?php
/**
 *
 * @author k.vagin
 */

namespace Core\Flow\Trace\Operators\Blocks;


class Stmt_While extends \Core\Flow\Trace\Operators\AOperator
{
	public function result()
	{
		$this->deep(array($this->node->cond));
		$this->deep($this->node->stmts);
	}

	private function deep(array $exprs)
	{
		foreach ($exprs as $expr) {
			$this->toTrace($expr);
		}
	}
} 