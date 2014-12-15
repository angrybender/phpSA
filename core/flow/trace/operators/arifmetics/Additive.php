<?php
/**
 * плюс и минус,
 * умножение
 * @author k.vagin
 */

namespace Core\Flow\Trace\Operators\Arifmetics;


use Core\Flow\Trace\Variable;

class Additive extends \Core\Flow\Trace\Operators\AOperator
{
	private $cast = array(
		Variable::TYPE_INT => array(
			Variable::TYPE_FLOAT	=> Variable::TYPE_FLOAT, // если целое умножается на дробное получается дробное (и по аналогии для всех остальных правил ниже)
			Variable::TYPE_STRING	=> Variable::TYPE_INT,
			Variable::TYPE_BOOLEAN	=> Variable::TYPE_INT,
		),

		Variable::TYPE_FLOAT => array(
			Variable::TYPE_STRING	=> Variable::TYPE_FLOAT,
			Variable::TYPE_INT		=> Variable::TYPE_FLOAT,
		),

		Variable::TYPE_STRING => array(
			Variable::TYPE_STRING	=> Variable::TYPE_INT,
			Variable::TYPE_FLOAT	=> Variable::TYPE_FLOAT,
			Variable::TYPE_INT		=> Variable::TYPE_INT,
		),
	);

	public function result()
	{
		$result_variable = new Variable();

		$variable = $this->scope->getVariable($this->node->left);
		$right_pair = $this->toTrace($this->node->right);

		if (!empty($variable) && !empty($right_pair)) {

			$types_of_variable = $variable->getScalarTypes();
			$types_of_expr 	   = $right_pair->getScalarTypes();

			// рассчитывает, какой тип может иметь результат, исходя из правил приведения типов
			$new_set_of_types = array();
			foreach ($types_of_expr as $type_of_expr) {
				foreach ($types_of_variable as $type_of_variable) {
					if ($type_of_expr === $type_of_variable) {
						$new_set_of_types[] = $type_of_expr;
					}
					elseif (!isset($this->cast[$type_of_expr], $this->cast[$type_of_expr][$type_of_variable])) {
						$new_set_of_types[] = $this->cast[$type_of_expr][$type_of_variable];
					}
				}
			}
			$result_variable->setScalarTypes($new_set_of_types);

			if ($variable->getIsNotEmpty() && $right_pair->getIsNotEmpty() && $this->node->getType() !== 'Expr_Minus') {
				$result_variable->setIsNotEmpty();
			}
		}

		return $result_variable;
	}
}