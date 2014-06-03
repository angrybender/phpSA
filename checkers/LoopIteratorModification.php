<?php
namespace Checkers;

class LoopIteratorModification
{
	protected $types = array(
		CHECKER_ERRORS
	);

	protected $error_message = 'Внутри тела цикла происходит модификация итератора, возможно стоит изменить инструктцию на while';

	protected $extractor = 'BlocksWithHead';
	protected $filter = array(
		'block' => 'T_FOR'
	); //фильтр для извлекателя

	public function check($code, $full_tokens)
	{
		$var = $this->get_variable($full_tokens['head']);

		if (!empty($var)) {
			return !$this->check_var_assets($full_tokens['body'], $var);
		}
	}

	/**
	 * извлечь имя итератора
	 * @param array $head
	 * @return string
	 */
	private function get_variable(array $head)
	{
		$var = '';

		foreach ($head as $i => $token)
		{
			if ($var === ''
				&& is_array($token)
				&& $token[0] === 'T_VARIABLE'
				&& isset($head[$i+1])
				&& ($head[$i+1] === '=' || $head[$i+1] === '<' || $head[$i+1] === '>' || $head[$i+1] === '<=' || $head[$i+1] === '>=')
			) {
				$var = $token[1];
				break;
			}
		}

		return $var;
	}

	/**
	 * проверяет факт изменения переменной внутри кода
	 * @param $body
	 * @param $var_name
	 * @return bool
	 */
	private function check_var_assets($body, $var_name)
	{
		$is_simple_eq = $this->check_equal($body, $var_name);
		if ($is_simple_eq) {
			return true;
		}

		$vars_by_ref = $this->variables_assets_by_procedure($body);
		return in_array($var_name, $vars_by_ref);
	}

	/**
	 * ищет $var = ...
	 * @param $body
	 * @param $var_name
	 * @return bool
	 */
	private function check_equal($body, $var_name)
	{
		foreach ($body as $i => $token)
		{
			if (\Tokenizer::tokens_is_eq($token, array('T_VARIABLE', $var_name), true)
				&& isset($body[$i+1])
				&& $body[$i+1] === '='
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * переменные, которые получают значение от функций, будучи переданными как аргумент. пример: preg_match($regexp, $mime, $matches)
	 * @param array
	 * @return array
	 */
	private function variables_assets_by_procedure(array $_tokens)
	{
		$callback_into_variable = array();
		foreach (\Repository::$function_callback_into_variable as $func_name => $arr_func_arg_pos) {
			$__tokens = $_tokens;
			while (true) {
				$function_callback_into_variable_pos = \Tokenizer::token_ispos($__tokens, $func_name, 'T_STRING');
				if ($function_callback_into_variable_pos === false) {
					break;
				}

				$__tokens = array_slice($__tokens, $function_callback_into_variable_pos+1);

				$expression = \Tokenizer::find_full_first_expression($__tokens,'(', ')', true);

				unset($expression[0]); // первая скобка

				foreach ($arr_func_arg_pos as $func_arg_pos) {
					$var_name = $this->extract_need_arg($expression, $func_arg_pos);

					if (!empty($var_name)) {
						$callback_into_variable[] = $var_name;
					}
				}
			}
		}

		foreach (\Repository::$function_callback_into_variable_infinity as $func_name => $func_arg_start_pos) {
			$__tokens = $_tokens;
			while (true) {
				$function_callback_into_variable_pos = \Tokenizer::token_ispos($__tokens, $func_name, 'T_STRING');
				if ($function_callback_into_variable_pos === false) {
					break;
				}

				$__tokens = array_slice($__tokens, $function_callback_into_variable_pos+1);

				$expression = \Tokenizer::find_full_first_expression($__tokens,'(', ')', true);

				unset($expression[0]); // первая скобка

				$_func_arg_start_pos = $func_arg_start_pos;
				while (true) {
					$var_name = $this->extract_need_arg($expression, $_func_arg_start_pos);

					if (!empty($var_name)) {
						$callback_into_variable[] = $var_name;
						$_func_arg_start_pos++;
					}
					else {
						break;
					}
				}
			}
		}

		return array_unique($callback_into_variable);
	}

	/**
	 * извлекает нужные аргумент из выражения
	 * @param array
	 * @param int
	 * @return array
	 */
	private function extract_need_arg(array $tokens, $pos = 0)
	{
		$args = \Expressions::extract_all_args($tokens);
		return isset($args[$pos-1]) ? str_replace(')', '', $args[$pos-1]) : '';
	}
}