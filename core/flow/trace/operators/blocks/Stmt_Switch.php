<?php
/**
 *
 * @author k.vagin
 */

namespace Core\Flow\Trace\Operators\Blocks;


class Stmt_Switch extends \Core\Flow\Trace\Operators\AOperator
{
	public function result()
	{
		// трассировка условия
		$this->toTrace($this->node->cond); // условие относится к области видимости всех веток ветвления

		/** @var $case \PHPParser_Node_Stmt_Case */
		foreach ($this->node->cases as $case) {

			$scope = clone $this->scope;

			if (!empty($case->cond)) {
				$operator = self::getOperator($case->cond);
				$operator->setScope($scope);
				$operator->setNode($case->cond);
				$operator->result(); // @todo сравнить типы данных в главном условии и тут
			}

			$this->deep($case->stmts, $scope);
		}
	}

	private function deep(array $nodes, $scope)
	{
		foreach ($nodes as $line) {
			$operator = self::getOperator($line);
			$operator->setScope($scope);
			$operator->setNode($line);
			$operator->result();
		}
	}
}