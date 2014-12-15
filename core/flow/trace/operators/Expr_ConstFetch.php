<?php
/**
 *
 * @author k.vagin
 */

namespace Core\Flow\Trace\Operators;


use Core\Flow\Trace\Variable;

class Expr_ConstFetch extends AOperator
{
	public function result()
	{
		$var = new Variable();
		$name = $this->node->name->parts;
		if (count($name) > 1) {

		}
		elseif ($name[0] === 'true') {
			$var->setScalarTypes(Variable::TYPE_BOOLEAN);
			$var->setIsNotEmpty();
		}
		elseif ($name[0] === 'false') {
			$var->setScalarTypes(Variable::TYPE_BOOLEAN);
			$var->setIsEmpty();
		}

		return $var;
	}
}