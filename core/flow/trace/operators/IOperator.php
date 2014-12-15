<?php
/**
 * "вычисляет" какую либо функцию над переданными(переданным) нодами(нодой)
 * @author k.vagin
 */

namespace Core\Flow\Trace\Operators;


interface IOperator
{
	public function setNode(\PHPParser_Node $node);

	public function setScope(\Core\Flow\Trace\Scope $scope);

	public function result();
}