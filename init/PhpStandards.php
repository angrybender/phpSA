<?php
/**
 * загружает инфу о пхп стандартах
 * @author k.vagin
 */

namespace Init;


class PhpStandards extends \Analisator\ParentInit
{
	public function __construct()
	{
		$data = file_get_contents(__DIR__ . '/../' . \Analisator\Config::$cache_path . '/php_standards_functions.txt');

		$this->full_functions_call(unserialize($data));
	}

	protected function full_functions_call($data)
	{
		foreach ($data as $function_name => $function_prototype) {

			// только для тех, кто принимает аргументы по ссылке
			if (!$this->is_have_byref_args($function_prototype)) {
				continue;
			}

			if ($function_prototype['ipf'] > 0) {
				$this->add_to_function_with_inf($function_name, $function_prototype);
			}
			else {
				$this->add_to_function($function_name, $function_prototype);
			}
		}
	}

	protected function is_have_byref_args($function_prototype)
	{
		return count(array_filter($function_prototype['args'], function($value){
			return $value;
		})) > 0;
	}

	protected function add_to_function_with_inf($function_name, $function_prototype)
	{
		\Core\Repository::$function_callback_into_variable_infinity[$function_name] = $function_prototype['ipf'];
	}

	protected function add_to_function($function_name, $function_prototype)
	{
		\Core\Repository::$function_callback_into_variable_infinity[$function_name] = array();
		foreach ($function_prototype['args'] as $pos => $arg) {
			if ($arg) {
				\Core\Repository::$function_callback_into_variable[$function_name][] = $pos+1;
			}
		}
	}
} 