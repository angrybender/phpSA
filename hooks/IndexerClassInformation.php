<?php
/**
 *
 * @author k.vagin
 */

namespace Hooks;

class IndexerClassInformation extends \Analisator\ParentHook {

	private $index_by_method_name_method_args_count = array();
	public $index_of_properties = array();

	public function run()
	{
		foreach (\Workers\ClassInformation::$instance->class_info as $class_info) {
			$this->class_treat($class_info['name'], $class_info['methods']);
			$this->prop_indexer($class_info['properties']);
		}
	}

	/**
	 * @param $class_name
	 * @param array $methods
	 */
	private function class_treat($class_name, array $methods)
	{
		foreach ($methods as $method) {

			$method['name'] = strtolower($method['name']);

			// индекс по имени и количеству аргументов
			$key = $method['name'] . '/' . count($method['args']);
			if (!isset($this->index_by_method_name_method_args_count[$key])) {
				$this->index_by_method_name_method_args_count[$key] = array();
			}

			$this->index_by_method_name_method_args_count[$key][] = $method;

			$key = $method['name'] . '/';
			if (!isset($this->index_by_method_name_method_args_count[$key])) {
				$this->index_by_method_name_method_args_count[$key] = array();
			}

			$this->index_by_method_name_method_args_count[$key][] = $method;
		}
	}

	private function prop_indexer(array $properties)
	{
		foreach ($properties as $var_name) {
			$this->index_of_properties[$var_name] = true;
		}
	}

	/**
	 * возвращает характеристики методов всех классов
	 * @param string $method_name
	 * @param $args_count		количество аргументов
	 * @return array
	 */
	public function find_all_methods($method_name = "", $args_count = 0)
	{
		if ($args_count === false) {
			$args_count = '';
		}

		$key = strtolower($method_name) . '/' . $args_count;
		if (!isset($this->index_by_method_name_method_args_count[$key])) {
			return false;
		}

		return $this->index_by_method_name_method_args_count[$key];
	}
} 