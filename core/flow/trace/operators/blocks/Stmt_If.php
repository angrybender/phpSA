<?php
/**
 * каждая из веток условного ветвления исполняется в паралельном потоке - с изолированной областью значений переменных
 * условие вклучается в эту область
 * @author k.vagin
 */

namespace Core\Flow\Trace\Operators\Blocks;


class Stmt_If extends \Core\Flow\Trace\Operators\AOperator
{
	public function result()
	{
		$this->ifBranch();
		$this->elseifBranch();
		$this->elseBranch();
	}

	private function ifBranch()
	{
		$scope = clone $this->scope;

		$operator = self::getOperator($this->node->cond);
		$operator->setScope($scope);
		$operator->setNode($this->node->cond);
		$operator->result();

		$this->deep($this->node->stmts, $scope);
	}

	private function elseifBranch()
	{
		foreach ($this->node->elseifs as $elseif) {
			$scope = clone $this->scope;

			$operator = self::getOperator($elseif->cond);
			$operator->setScope($scope);
			$operator->setNode($elseif->cond);
			$operator->result();

			$this->deep($elseif->stmts, $scope);
		}
	}

	private function elseBranch()
	{
		$this->deep($this->node->else->stmts, clone $this->scope);
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