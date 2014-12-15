<?php
/**
 *
 * @author k.vagin
 */

namespace Core\Flow\Trace\Operators\Bit;


use Core\Flow\Trace\Variable;

class Operator extends \Core\Flow\Trace\Operators\AOperator
{
	public function result()
	{
		if ($this->node->getType() !== 'Expr_BitwiseNot') {
			$this->toTrace($this->node->left);
			$this->toTrace($this->node->right);
		}
		else {
			$this->toTrace($this->node->expr);
		}

		$int = new Variable();
		$int->setScalarTypes(Variable::TYPE_INT);
		return $int;
	}
}