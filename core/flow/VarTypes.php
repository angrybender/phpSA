<?php
namespace Core\Flow;

/**
 * вычисленный тип переменной
 * у переменной может быть несколько альтернативных типов
 * @author k.vagin
 */


class VarTypes
{
	/**
	 * имя переменной
	 * @var string
	 */
	protected $name = '';

	const
		CLASS_SCALAR = 10,
		CLASS_VECTOR = 20,
		CLASS_OBJECT = 30,
		CLASS_UNDEF  = 40,		// неопределена
		CLASS_MIX  	 = 90;		// любое значение

	/**
	 * классы типа переменной (скаляр, веткор, объект)
	 * @var int[]
	 */
	protected $class = array();

	const
		TYPE_INT 		= 100,
		TYPE_FLOAT 		= 110,
		TYPE_STRING 	= 120,
		TYPE_OBJECT 	= 130,
		TYPE_BOOLEAN 	= 140;


	/**
	 * типы переменной
	 * @var int[]
	 */
	protected $type = array();

	/**
	 * имена классов которые переменная имплементирует, или NULL
	 * @var array
	 */
	protected $sub_type = array();

	/**
	 * возможные значения, может содержать ссылку на др. переменную
	 * @var array
	 */
	protected $values = array();


	public function __construct($name)
	{
		$this->name = $name;
		$this->class = array(self::CLASS_UNDEF);
	}

	public function setClass($class)
	{
		if (!in_array($class, $this->class)) {
			$this->class[] = $class;
		}
	}

	public function setType($type)
	{
		if (!in_array($type, $this->type)) {
			$this->type[] = $type;
		}
	}

	public function setSubType($type)
	{
		if (!in_array($type, $this->sub_type)) {
			$this->sub_type[] = $type;
		}
	}

	public function setValue($value)
	{
		if (!in_array($value, $this->values)) {
			$this->values[] = $value;
		}
	}
}