<?php
/**
 *
 * @author k.vagin
 */

namespace Core\Flow\Trace\Operators\Blocks;


class Expr_FuncCall extends \Core\Flow\Trace\Operators\AOperator
{
	public function result()
	{
		foreach ($this->node->args as $argument) {
			$this->toTrace($argument->value);

			// @todo if value != Expr_var && byRef = true WTF
		}
	}
}