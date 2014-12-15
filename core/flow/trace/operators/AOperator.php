<?php
/**
 *
 * @author k.vagin
 */

namespace Core\Flow\Trace\Operators;


class AOperator implements IOperator
{
	/**
	 * @var \PHPParser_Node
	 */
	protected $node;

	/**
	 * @var \Core\Flow\Trace\Scope
	 */
	protected $scope;

	public function setNode(\PHPParser_Node $node)
	{
		$this->node = $node;
	}

	public function setScope(\Core\Flow\Trace\Scope $scope)
	{
		$this->scope = $scope;
	}

	public function result()
	{
		return array();
	}

	public static function getOperator(\PHPParser_Node $node)
	{
		return \Core\Flow\Trace\Locator::locate($node->getType());
	}

	/**
	 * @param \PHPParser_Node $node
	 * @param bool $new_scope
	 * @return \Core\Flow\Trace\Variable|null
	 */
	public function toTrace(\PHPParser_Node $node, $new_scope = false)
	{
		if ($node instanceof \PHPParser_Node_Expr_PropertyFetch
			|| $node instanceof \PHPParser_Node_Expr_Variable
			|| $node instanceof \PHPParser_Node_Expr_ArrayDimFetch) {
			$variable = $this->scope->getVariable($node);
			if (!empty($variable)) {
				return $variable;
			}
		}

		$operator = self::getOperator($node);
		$operator->setNode($node);

		if (!$new_scope) {
			// внутри текущего скоупа
			$operator->setScope($this->scope);
		}
		else {
			// создаем изолированный скоуп (новая ветка выполнения):
			$operator->setScope(clone $this->scope);
		}

		return $operator->result();
	}
}