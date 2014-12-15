<?php
/**
 *
 * @author k.vagin
 */

namespace Core\Flow\Trace\Operators;


use Core\Flow\Trace\Variable;

class Expr_Array extends AOperator
{
	public function result()
	{
		$array = new Variable();
		$array->setIsArray();

		foreach ($this->node->items as $item) {
			$this->toTrace($item->value);
		}

		if (empty($this->node->items)) {
			$array->setIsEmpty();
		}
		else {
			$array->setIsNotEmpty();
		}

		return $array;
	}
}