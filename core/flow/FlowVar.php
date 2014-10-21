<?php

namespace Core\Flow;

/**
 * поток выполнения программы с проверкой типов переменных
 * @author k.vagin
 */
class FlowVar
{
	protected $namespace;
	protected $class;

	/**
	 * @param \PHPParser_Node[]|PHPParser_Node $nodes
	 * @param null $namespace
	 * @param null $class
	 * @param array $scope	значения переменных
	 */
	public function __construct($nodes, $namespace = null, $class = null, array $scope = array())
	{
		$this->scope = $scope;
		$this->namespace = $namespace;
		$this->class = $class;

		$this->trace($nodes);
	}

	/**
	 * @param \PHPParser_Node[]|PHPParser_Node $nodes
	 */
	protected function trace($nodes)
	{

	}
} 