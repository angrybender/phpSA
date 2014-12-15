<?php
/**
 *
 * @author k.vagin
 */

namespace Core\Flow\Trace\Operators\Comparations;


use Core\Flow\Trace\Variable;

class Expr_Equal extends \Core\Flow\Trace\Operators\AOperator
{
	public function result()
	{
		$var = new Variable();
		$var->setScalarTypes(Variable::TYPE_BOOLEAN);

		$left_var = $this->toTrace($this->node->left);
		$right_var = $this->toTrace($this->node->right);

		if (!empty($left_var) && !empty($right_var)) {
			if ($left_var->getIsEmpty() !== $right_var->getIsEmpty() || $left_var->getIsNotEmpty() !== $right_var->getIsNotEmpty()) {
				$var->setIsEmpty();
			}
		}

		return $var;
	}
}