<?php
/**
 *
 * @author k.vagin
 */

namespace Core\Flow\Trace\Operators\Comparations;

use \Core\Flow\Trace\Variable;

class Expr_Smaller extends \Core\Flow\Trace\Operators\AOperator
{
	public function result()
	{
		$main_var = new Variable();
		$main_var->setScalarTypes(Variable::TYPE_BOOLEAN);
		return $main_var;
	}
}