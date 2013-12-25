<?php
/**
 * json_decode может возвращать массив
 * проверяется, не производится ли ручная конвертация результата работы ф-ии json_decod
 * @author k.vagin
 */

namespace Checkers;


class JsonDecodeArrayCan extends \Analisator\ParentChecker
{

	protected $types = array(
		CHECKER_HEURISTIC
	);

	protected $error_message = 'возможно лишняя конвертация, json_decode может сразу возвращать array, если установить второй аргумент в true';

	protected $extractor = 'Full'; // класс-извлекатель нужных блоков

	protected $is_line_return = true; // по умолчанию, строка ошибки определяется по началу блока, но функция проверки  может ее переопределить
	protected $line = array();

	private $tokens = array();
	private $lines = array();

	/**
	 * @param array $tokens
	 * @return bool
	 */
	public function check($tokens)
	{
		$tokens = array_map(function($val){
			if (is_array($val)) {
				$val[1] = strtolower($val[1]);
			}

			return $val;
		}, $tokens);

		$this->tokens = $tokens;
		$this->lines = \Tokenizer::format_code_into_lines($tokens);

		$suspicious_vars = array();
		foreach ($tokens as $i => $token) {
			if (is_array($token)
				&& $token[0] === 'T_STRING'
				&& isset($tokens[$i+1])
				&& $tokens[$i+1][0] == '('
				&& strtolower($token[1]) === 'json_decode'
				&& ($i>1)
				&& (
					$tokens[$i-1] === '='
					||
					is_array($tokens[$i-1]) && $tokens[$i-1][0] !== 'T_DOUBLE_COLON' && $tokens[$i-1][0] !== 'T_OBJECT_OPERATOR'
				)
			) {

				$_args = substr(\Tokenizer::find_full_first_expression(array_slice($tokens, $i+1), '(', ')'), 1);
				$is_assoc = $this->extract_assoc_arg($_args);

				if ($is_assoc !== 'true') {
					$suspicious_vars[] = array(
						'name' => \Tokenizer::get_assignment_variable_name($tokens, $i),
						'line' => $token[2]
					);
				}

			}
		}

		foreach ($suspicious_vars as $var_name) {
			if (empty($var_name['name'])) {
				continue;
			}

			if ($this->is_var_in_array_operation($var_name['name'])) {
				$this->line[] = $var_name['line'];
			}
		}

		return empty($this->line);
	}

	/**
	 * извлекает аргумент assoc
	 * @param string
	 * @return array
	 */
	private function extract_assoc_arg($_args = "")
	{
		$tokens = \Tokenizer::get_tokens_of_expression($_args);

		// убиваем все, что внутри вложенных скобок (если есть), чтобы запятые посчитать верно
		// todo вынести в отд. метод ядерных классов
		$brackets_cnt = 0;
		foreach ($tokens as $i => $token) {
			if ($token === '(') {
				$brackets_cnt++;
			}

			if ($token === ')') {
				$brackets_cnt--;
			}

			if ($brackets_cnt>0) {
				unset($tokens[$i]);
			}
		}
		$tokens = array_values($tokens);

		$comma_cnt = 0;
		$result_tokens = array();
		foreach ($tokens as $i => $token) {
			if ($token === ',') {
				$comma_cnt++;
			}

			if ($comma_cnt === 1) {
				$result_tokens = array_slice($tokens, $i+1);
				break;
			}
		}

		return !empty($result_tokens)
				? substr(strtolower(\Tokenizer::tokens_to_source($result_tokens)), 0, -1) // отрезаем оконечную скобку
				: false;
	}

	/**
	 * участвует ли переменная в операциях с массивами
	 * @param $var_name
	 * @return bool
	 */
	private function is_var_in_array_operation($var_name)
	{
		$needle = \Tokenizer::get_tokens_of_expression($var_name);
		$need_as_array = $needle;
		$need_as_array[] = '[';

		$convert = \Tokenizer::get_tokens_of_expression('(array)');

		foreach ($this->lines as $line) {

			$var_pos = \Tokenizer::token_find($line, $needle);
			if ($var_pos === false) continue;

			$convert_pos = \Tokenizer::token_find($line, $convert);
			if ($convert_pos !== false) {
				return true;
			}

			$as_array = \Tokenizer::token_find($line, $need_as_array);
			if ($as_array !== false) {
				return true;
			}
		}

		return false;
	}
} 