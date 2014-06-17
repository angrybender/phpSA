<?php
/**
 * собирает публичные методы и свойства каждого класса
 * @author k.vagin
 */

namespace Workers;


class ClassInformation extends \Analisator\ParentWorker {

	/**
	 * @var array
	 */
	public $class_info = array();

	public function work($file)
	{
		$tokens = \Core\Tokenizer::parse_file($file);
		if ($tokens instanceof \Exception) {
			return;
		}

		$classes = \Core\AST::find_tree_by_root($tokens, array(
			'PHPParser_Node_Stmt_Class',
			'PHPParser_Node_Stmt_Interface',
		));

		foreach ($classes as $class) {

			$methods_and_prop = \Core\AST::find_tree_by_root($class->stmts, array(
				'PHPParser_Node_Stmt_ClassMethod',
				'PHPParser_Node_Stmt_Property'
			));

			$this->class_info[] = array(
				'name' => $class->name,
				'methods' => $this->extract_class_methods_with_declarations($methods_and_prop),
				'properties' => $this->extract_properties($methods_and_prop)
			);
		}
	}

	/**
	 * @param \PHPParser_Node[] $class_body
	 * @return array
	 */
	protected function extract_class_methods_with_declarations($class_body)
	{
		$procedures = array();

		foreach ($class_body as $method) {
			if (!($method instanceof \PHPParser_Node_Stmt_ClassMethod)) {
				continue;
			}

			if (!$method->isPublic()) {
				continue;
			}

			$procedures[] = array(
				'name' => $method->name,
				'args' => array_map(function($value) {
					if ($value->byRef) {
						return 'BY_LINK';
					}
					else {
						return 'BY_VAL';
					}
				}, $method->params)
			);
		}

		return $procedures;
	}

	/**
	 * @param \PHPParser_Node[] $class_body
	 * @return array
	 */
	protected function extract_properties($class_body)
	{
		$properties = array();

		foreach ($class_body as $prop) {
			if (!($prop instanceof \PHPParser_Node_Stmt_Property)) {
				continue;
			}

			if (!$prop->isPublic()) {
				continue;
			}

			foreach ($prop->props as $property) {
				$properties[] = $property->name;
			}
		}

		return $properties;
	}
}

/**
 * пример вывода:
 *
Array
(
  Array
 (
	[name] => CI_Output
	[methods] => Array
	(
		[0] => Array

			[name] => __construct
			[args] => Array	// аргументов нет
			(
			)

		[2] => Array

			[name] => set_output
			[args] => Array
			(
				[0] => BY_VAL //  единственный аргумент передается по значению
			)

		[12] => Array

			[name] => _display_cache
			[args] => Array
			(
				[0] => BY_LINK
				[1] => BY_LINK	// второй аргумент передается по ссылке
			)
	)

))

 */