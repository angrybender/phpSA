<?php
/**
 * точка входа для трассирования кода
 * инициализирует область видимости (scope), которая хранит перемененные (класс Variable) и их вычисленные типы данных
 * каждая инструкция (или группа инструкций) языка обабатывается своим оператором-классом (IOperator)
 * при необходимости, скоуп "раздваивается", например когда идет трассировка условного ветвления
 * и мы имеем 2 паралельных состояния программы - когда условие истинно ,и когда ложно
 * @author k.vagin
 */

namespace Core\Flow\Trace;


use Core\Flow\Trace\Operators\AOperator;

class Tracer
{
	/**
	 * @var Scope
	 */
	private $scope;

	/**
	 * @var \PHPParser_Node[]
	 */
	private $nodes = array();

	public function __construct($nodes)
	{
		$this->scope = new Scope();
		$this->nodes = !is_array($nodes) ? array($nodes) : $nodes;
	}

	public function trace()
	{
		foreach ($this->nodes as $node) {
			$operator = AOperator::getOperator($node);
			$operator->setNode($node);
			$operator->setScope($this->scope);
			$operator->result();
		}
	}

	public function getScope()
	{
		return $this->scope;
	}
}