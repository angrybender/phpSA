<?php
/**
 *
 * @author k.vagin
 */

namespace Core\Flow\Trace\Operators\Blocks;


use Core\Flow\Trace\Variable;

class Stmt_Foreach extends \Core\Flow\Trace\Operators\AOperator
{
	public function result()
	{
		if (!empty($this->node->keyVar)) {
			// итератор цикла добавляем в скоуп, если он определен
			$iterator = new Variable();
			$iterator->addScalarType(Variable::TYPE_INT);
			$iterator->addScalarType(Variable::TYPE_STRING);
			$iterator->setName($this->node->keyVar);
			$this->scope->addVariable($iterator);
		}

		$this->deep($this->node->stmts);
		$this->deep(array($this->node->expr));
	}

	private function deep(array $exprs)
	{
		foreach ($exprs as $expr) {
			$this->toTrace($expr);
		}
	}
} 