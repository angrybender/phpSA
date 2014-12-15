<?php
/**
 *
 * @author k.vagin
 */

namespace Core\Flow\Trace;


class Variable
{
	const
		TYPE_INT 		= 'int',
		TYPE_FLOAT 		= 'float',
		TYPE_STRING 	= 'string',
		TYPE_BOOLEAN 	= 'bool',
		TYPE_RES		= 'resource', // ресурс
		TYPE_NULL		= 'null';

	public function setIsArray()
	{
		$this->is_array = true;
	}

	/**
	 * @return boolean
	 */
	public function getIsArray()
	{
		return $this->is_array;
	}

	public function setIsEmpty()
	{
		$this->is_empty = true;
	}

	/**
	 * @return boolean
	 */
	public function getIsEmpty()
	{
		return $this->is_empty;
	}

	public function setIsNotEmpty()
	{
		$this->is_not_empty = true;
	}

	/**
	 * @return boolean
	 */
	public function getIsNotEmpty()
	{
		return $this->is_not_empty;
	}

	public function setIsObject()
	{
		$this->is_object = true;
	}

	/**
	 * @return boolean
	 */
	public function getIsObject()
	{
		return $this->is_object;
	}

	/**
	 * @param array $scalar_type
	 */
	public function setScalarTypes($scalar_type)
	{
		$this->scalar_types = (array)$scalar_type;
	}

	public function addScalarType($scalar_type)
	{
		$this->scalar_types[] = $scalar_type;
		$this->scalar_types = array_unique($this->scalar_types);
	}

	/**
	 * @return array
	 */
	public function getScalarTypes()
	{
		return $this->scalar_types;
	}

	private $is_array = false;
	private $is_object = false;

	private $is_not_empty = false; // if ($a) {}
	private $is_empty = false;	// if (!$a) {}

	private $scalar_types = array();

	private $name = '';

	private $value;

	/**
	 * @param mixed $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		if ($name instanceof \PHPParser_Node) {
			$name = \Core\Tokenizer::cleanPrinter($name);
		}

		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	public function merge(Variable $variable)
	{
		$this->scalar_types = array_unique(array_merge($this->scalar_types, $variable->getScalarTypes()));
		$this->is_array = $this->is_array && $variable->getIsArray();
		$this->is_object = $this->is_object && $variable->getIsObject();
		$this->is_empty = $this->is_empty && $variable->getIsEmpty();
		$this->is_not_empty = $this->is_not_empty && $variable->getIsNotEmpty();
	}

	public function resetEmptyStatus()
	{
		$this->is_not_empty = false;
		$this->is_empty = false;
	}

	public function hasType($type_name)
	{
		return in_array($type_name, $this->scalar_types);
	}
} 