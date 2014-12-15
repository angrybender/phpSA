<?php
/**
 *
 * @author k.vagin
 */

namespace Core\Flow\Trace\Operators;

use Core\Flow\Trace\Variable;

class Scalar_Operator extends AOperator
{
	/**
	 * соответствие между некоторыми типами объектов, составляющих константное выражение и внутренних типах проекта
	 */
	private static $node_types = array(
		'Scalar_LNumber' => Variable::TYPE_INT,
		'Scalar_DNumber' => Variable::TYPE_FLOAT,
		'Scalar_String' => Variable::TYPE_STRING,
		'Scalar_Encapsed' => Variable::TYPE_STRING,
		'Scalar_DirConst' => Variable::TYPE_STRING,
		'Scalar_FuncConst' => Variable::TYPE_STRING,
		'Scalar_LineConst' => Variable::TYPE_INT,
		'Scalar_MethodConst' => Variable::TYPE_STRING,
		'Scalar_NSConst' => Variable::TYPE_STRING,
		'Scalar_TraitConst' => Variable::TYPE_STRING,
	);

	/**
	 * заведомо не пустые константы
	 * @var array
	 */
	private static $node_types_not_empty = array(
		'Scalar_DirConst' => Variable::TYPE_STRING,
		'Scalar_LineConst' => Variable::TYPE_INT,
	);

	/**
	 * извлечь данные из скалярного значения
	 * @param \PHPParser_Node $node
	 * @return \Core\Flow\Trace\Variable
	 */
	private function extractValue(\PHPParser_Node $node)
	{
		$type = $node->getType();
		$var = new Variable();
		$var->setScalarTypes(self::$node_types[$type]);
		$value = $node->value;

		$var->setValue($value);
		if ($type === 'Scalar_LNumber' && $value != 0) {
			$var->setIsNotEmpty();
		}
		elseif ($type === 'Scalar_LNumber' && $value == 0) {
			$var->setIsEmpty();
		}
		elseif ($type === 'Scalar_DNumber' && $value == 0) {
			$var->setIsEmpty();
		}
		elseif ($type === 'Scalar_DNumber' && $value != 0) {
			$var->setIsNotEmpty();
		}
		elseif ($var->getScalarTypes() === Variable::TYPE_STRING && $value != "") {
			$var->setIsNotEmpty();
		}
		elseif ($var->getScalarTypes() === Variable::TYPE_STRING && $value == "") {
			$var->setIsEmpty();
		}

		if (isset(self::$node_types_not_empty[$type])) {
			$var->setIsNotEmpty();
		}

		return $var;
	}

	public function result()
	{
		return $this->extractValue($this->node);
	}
}