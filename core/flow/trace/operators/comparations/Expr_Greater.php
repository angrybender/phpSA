<?php
/**
 *
 * @author k.vagin
 */

namespace Core\Flow\Trace\Operators\Comparations;

use \Core\Flow\Trace\Variable;

class Expr_Greater extends \Core\Flow\Trace\Operators\AOperator
{
	public function result()
	{
		$main_var = new Variable();
		$name = \Core\Tokenizer::cleanPrinter($this->node->left);

		/** @var \Core\Flow\Trace\Variable $tmp_var */
		$tmp_var = $this->toTrace($this->node->right);
		$var_type = $tmp_var->getScalarTypes();
		if ($var_type === Variable::TYPE_FLOAT || $var_type === Variable::TYPE_INT) {
			if ($tmp_var->getValue() >= 0) {
				$main_var->setIsNotEmpty();
			}
		}

		$main_var->setName($name);
		$main_var->setScalarTypes($var_type);
		$tmp_var = null;

		$this->scope->setVariable($main_var);
	}
}