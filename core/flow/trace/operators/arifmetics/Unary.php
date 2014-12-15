<?php
/**
 *
 * @author k.vagin
 */

namespace Core\Flow\Trace\Operators\Arifmetics;


use Core\Flow\Trace\Variable;

class Unary extends \Core\Flow\Trace\Operators\AOperator
{
	public function result()
	{
		if ($this->node->getType() === 'Expr_UnaryMinus') {
			$variable = $this->toTrace($this->node->expr);
		}
		else {
			$variable = $this->toTrace($this->node->var);
		}

		$return_var = clone $variable;
		if (!$variable->hasType(Variable::TYPE_INT)) {
			$return_var->setScalarTypes(Variable::TYPE_INT);
		}

		$return_var->resetEmptyStatus(); // ничего нельзя сказать точно о том, будет переменная содержать пусто или нет
		return $return_var;
	}
}