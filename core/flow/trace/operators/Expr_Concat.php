<?php
/**
 *
 * @author k.vagin
 */

namespace Core\Flow\Trace\Operators;


use Core\Flow\Trace\Variable;

class Expr_Concat extends AOperator
{
	public function result()
	{
		$string = new Variable();
		$string->setScalarTypes(Variable::TYPE_STRING);

		$left = $this->toTrace($this->node->left);
		$right = $this->toTrace($this->node->right);

		if (!empty($left) && !empty($right) && ($left->getIsNotEmpty() || $right->getIsNotEmpty())) {
			$string->setIsNotEmpty();
		}

		return $string;
	}
} 