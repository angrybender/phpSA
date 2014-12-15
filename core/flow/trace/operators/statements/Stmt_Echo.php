<?php
/**
 *
 * @author k.vagin
 */

namespace Core\Flow\Trace\Operators\Statements;


class Stmt_Echo extends \Core\Flow\Trace\Operators\AOperator
{
	public function result()
	{
		foreach ($this->node->exprs as $expr) {
			$this->toTrace($expr);
		}
	}
} 