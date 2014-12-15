<?php
/**
 *
 * @author k.vagin
 */

namespace Core\Flow\Trace\Operators\Arifmetics;

use Core\Flow\Trace\Variable;

class Expr_Mod extends \Core\Flow\Trace\Operators\AOperator
{
	public function result()
	{
		$result_variable = new Variable();
		$result_variable->setScalarTypes(Variable::TYPE_INT);
		return $result_variable;
	}
} 