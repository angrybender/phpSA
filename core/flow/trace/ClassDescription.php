<?php
/**
 * объект-описание класса/интерфейса и тд
 * @author k.vagin
 */

namespace Core\Flow\Trace;


class ClassDescription
{
	public $name_space = array();

	public $object = '';

	public $is_static = false; // @todo

	public function __construct(array $name_space, $name)
	{
		$this->name_space = $name_space;
		$this->object = $name;
	}

	public function setIsStatic()
	{
		$this->is_static = true;
	}

	/**
	 * @return boolean
	 */
	public function getIsStatic()
	{
		return $this->is_static;
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
}