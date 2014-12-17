<?php
/**
 * объект-описание вызываемой функции
 * @author k.vagin
 */

namespace Core\Flow\Trace;


class MethodCallDescription
{
	/**
	 * @var \Core\Flow\Trace\Variable[]
	 */
	public $parameters = array();

	/**
	 * @var \Core\Flow\Trace\Variable|null
	 */
	public $return = null;

	public $name_space = array();

	public $object = '';

	public $is_static = false; // @todo

	public function __construct(array $name_space, $object, array $params, Variable $return)
	{
		$this->parameters = $params;
		$this->return = $return;
		$this->name_space = $name_space;
		$this->object = $object;
	}

	/**
	 * @param string $object
	 */
	public function setObject($object)
	{
		$this->object = $object;
	}

	/**
	 * @return string
	 */
	public function getObject()
	{
		return $this->object;
	}

	/**
	 * @param array $name_space
	 */
	public function setNameSpace(array $name_space)
	{
		$this->name_space = $name_space;
	}

	/**
	 * @return array
	 */
	public function getNameSpace()
	{
		return $this->name_space;
	}

	/**
	 * @param \Core\Flow\Trace\Variable[] $parameters
	 */
	public function setParameters(array $parameters)
	{
		$this->parameters = $parameters;
	}

	/**
	 * @return \Core\Flow\Trace\Variable[]
	 */
	public function getParameters()
	{
		return $this->parameters;
	}

	/**
	 * @param \Core\Flow\Trace\Variable|null $return
	 */
	public function setReturn(Variable $return)
	{
		$this->return = $return;
	}

	/**
	 * @return \Core\Flow\Trace\Variable|null
	 */
	public function getReturn()
	{
		return $this->return;
	}
}