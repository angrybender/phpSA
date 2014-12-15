<?php
/**
 *
 * @author k.vagin
 */

namespace Core\Flow\Trace\Operators;

class Expr_Assign extends AOperator
{
	public function result()
	{
		$expression = $this->toTrace($this->node->expr);
		$expression->setName($this->node->var);
		$this->scope->setVariable($expression);

		return $expression; // есть цепочечное равенство, пример: $a = $b = 1;
	}
}