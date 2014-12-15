<?php
/**
 * @author k.vagin
 */

namespace Core\Flow\Trace\Operators\Arifmetics;

use Core\Flow\Trace\Variable;

class Div extends \Core\Flow\Trace\Operators\AOperator
{
	public function result()
	{
		$result_variable = new Variable();
		$result_variable->setScalarTypes(Variable::TYPE_FLOAT);
		return $result_variable;
	}
} 