<?php
/**
 * собирает методы и свойства каждого класса
 * @author k.vagin
 */

namespace Workers;


class ClassInformation extends \Analisator\ParentWorker {

	/**
	 * @var array
	 */
	public $class_info = array();

	public function work($source_code)
	{
		$tokens = \Tokenizer::get_tokens($source_code);

		$e_obj = new \Extractors\Classes($tokens);
		$classes = $e_obj->extract($tokens);
		unset($e_obj);

		foreach ($classes as $class) {
			$this->class_info[] = array(
				'name' => $class['name'],
				'methods' => $this->extract_function_with_declarations($class['body']),
				'properties' => $this->extract_properties($class['body'])
			);
		}
	}

	private function extract_function_with_declarations($class_body_tokens)
	{
		// нам нужно извлечь переданные переменные, поэтому стандартный извлекатель не подходит
		// todo дублирование
		$procedures = array();
		foreach ($class_body_tokens as $i => $token) {
			if (is_array($token)
				&& $token[0] === 'T_FUNCTION'
				&& isset($class_body_tokens[$i+1])
				&& is_array($class_body_tokens[$i+1])
				&& $class_body_tokens[$i+1][0] == 'T_STRING'
			) {
				$open_block_position = \Tokenizer::token_ispos(array_slice($class_body_tokens, $i), '{');

				if ($open_block_position !== false) {
					$function_declaration = array_slice($class_body_tokens, $i+2, $open_block_position-2);

					$procedures[] = array(
						'declaration' => $function_declaration,
						'name' => $class_body_tokens[$i+1][1]
					);
				}
			}
		}

		$class_body_tokens = null;

		// переформатирование деклараций методов в структуру инфы о аргументах
		foreach ($procedures as $j => $procedure) {
			$args = $procedure['declaration'];
			array_shift($args); // убираем обрамляющие скобки
			array_pop($args);

			$args_info = array();
			foreach ($args as $i => $arg_var) {
				if (is_array($arg_var)
					&& $arg_var[0] === 'T_VARIABLE'
					&& ($i>0)
					&& $args[$i-1] === '&'
				) {
					$args_info[] = 'BY_LINK';
					continue;
				}

				if (is_array($arg_var)
					&& $arg_var[0] === 'T_VARIABLE'
				) {
					$args_info[] = 'BY_VAL';
				}
			}

			unset($procedures[$j]['declaration']);
			$procedures[$j]['args'] = $args_info;
		}

		return $procedures;
	}

	private function extract_properties($class_body_tokens)
	{
		$types = array(
			'T_PRIVATE',
			'T_PUBLIC',
			'T_PROTECTED',
			'T_STATIC'
		);

		$properties = array();
		foreach ($class_body_tokens as $i => $token) {
			if ($i == 0) continue;

			if (is_array($token)
				&& $token[0] === 'T_VARIABLE'
				&& is_array($class_body_tokens[$i-1])
				&& in_array($class_body_tokens[$i-1][0], $types)
			) {
				$properties[] = $token[1];
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