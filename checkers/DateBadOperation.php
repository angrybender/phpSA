<?php
/**
 * пытается определить вероятный говнокод при обработке даты (неоптимальное использованение языка, неприменение существующих ф-ий и тд)
 * @author k.vagin
 */

namespace Checkers;

class DateBadOperation extends \Analisator\ParentChecker
{

	protected $types = array(
		CHECKER_HEURISTIC
	);

	protected $error_message = 'Подозрение на неоптимальную обработку даты/времени';

	protected $extractor = 'Procedure'; // класс-извлекатель нужных блоков

	private $suspicious_date_functions = array(
		'date',
		'time',
		'mktime',
		'gmmktime',
		'strtotime',
		'timestamp'
	);

	private $suspicious_string_functions = array(
		'explode',
		'substr',
		'preg_replace',
		'preg_match',
		'str_replace',
		'strpos',
		'strtr',
		'vsprintf',
		'sprintf'
	);

	private $tokens = array();
	private $code_lines = array();

	/**
	 * @param $code
	 * @return bool
	 */
	public function check($code)
	{
		if (is_scalar($code)) {
			if (!\Tokenizer::is_open_tag($code)) {
				$code = '<?php ' . $code;
			}

			$this->tokens = \Tokenizer::get_tokens($code, true);
		}
		else {
			$this->tokens = $code;
			$code = \Tokenizer::tokens_to_source($code);
		}

		if (!$this->is_date_and_string_operation_exist()) {
			return true; // чекеру нечего анализировать
		}

		$all_vars_count = count(\Variables::get_all_vars_in_expression($code, true));
		if ($all_vars_count == 0) {
			return true; // strange %)
		}

		$this->code_lines = \Tokenizer::format_code_into_lines($this->tokens);

		// дальше попытка вывести какую то интегральную метрику кода на основании применения тех или иных переменных и ф-ий
		// в общем - пытаемся определить кашу в коде

		$variables_near_date = $this->extract_variables_near_date();
		$variables_near_date = array_merge($variables_near_date, $this->extract_variables_near_variables($variables_near_date));

		$variables_near_str_operations = $this->extract_variables_near_str_operations();
		$variables_near_str_operations = array_merge($variables_near_str_operations, $this->extract_variables_near_variables($variables_near_str_operations));

		$intersect = array_intersect($variables_near_date, $variables_near_str_operations);

		$intersect_count = count($intersect)/$all_vars_count;
		if ($intersect_count<=0.25) return true;

		$evristic_points = $this->code_saturation(array_merge($variables_near_date, $variables_near_str_operations));

		//echo $evristic_points, PHP_EOL, $intersect_count, PHP_EOL;

		// *баная магия
		return !($intersect_count>0.75 || $evristic_points>=0.35);
	}

	/**
	 * первичная проверка на работу с датой и строками
	 * @return bool
	 */
	private function is_date_and_string_operation_exist()
	{
		$functions = \Procedures::get_all_procedures_in_code(array_slice($this->tokens, 0, round(count($this->tokens)/2))); // эвристика - ищем в первой половине кода

		// хардкод откровенного пиздеца: todo refactoring
		if ((count($functions)<=6) && in_array('date', $functions) && in_array('explode', $functions) && in_array('mktime', $functions)) {
			return true;
		}

		$date_fc = count(array_intersect($functions, $this->suspicious_date_functions));

		if ($date_fc === 0) return false;

		$str_fc = count(array_intersect($functions, $this->suspicious_string_functions));
		if ($str_fc < 2 && !$this->is_suspicious_arrays()) return false;

		return true;
	}

	/**
	 * пытается угадать - похож ли данный массив на массив из дней недели
	 * @param array $array_tokens
	 * @return bool
	 */
	private function check_array_is_may_weekdays(array $array_tokens)
	{
		if ($array_tokens['size'] !== 7) {
			return false;
		}

		$string_const_cnt = 0;

		foreach ($array_tokens['tokens'] as $i => $token) {
			if (is_array($token) && $token[0] === 'T_CONSTANT_ENCAPSED_STRING') {
				$string_const_cnt++;
			}
		}

		if ($string_const_cnt === 7) {
			return true;
		}

		return false;
	}

	/**
	 * пытается угадать - похож ли данный массив на массив из месяцев
	 * @param array $array_tokens
	 * @return bool
	 */
	private function check_array_is_may_months(array $array_tokens)
	{
		if ($array_tokens['size'] < 12 || $array_tokens['size'] > 13) {
			return false;
		}

		// т.к. отсчет месяцев в ряде ф-ий идет с нуля, часто создают массив в 13 элементов
		// при этом в первом пишут либо что то empty либо что то вроде "нулябрь"
		try {
			$factor_13 = true;
			if ($array_tokens['size'] === 13) {
				$factor_13 = false;
				$first_elem = $array_tokens['tokens'][1];
				$first_elem[1] = strtolower($first_elem[1]);

				if ($first_elem[0] == 'T_CONSTANT_ENCAPSED_STRING') {
					$factor_13 = true;
				}
				elseif ($first_elem[1] === 'null' || $first_elem[1] === 'false') {
					$factor_13 = true;
				}
			}

			$string_const_cnt = 0;

			foreach ($array_tokens['tokens'] as $i => $token) {
				if (is_array($token) && $token[0] === 'T_CONSTANT_ENCAPSED_STRING') {
					$string_const_cnt++;
				}
			}

			if (($string_const_cnt === 12 || $string_const_cnt === 13) && $factor_13) {
				return true;
			}
		}
		catch (\Exception $e) {
			return false;
		}

		return false;
	}

	/**
	 * ищет массивы, которые вероятно могут содержать дни недели или месяцы (характерно для пахнущего кода)
	 */
	public function is_suspicious_arrays()
	{
		$arr_arrays = \Variables::get_all_arrays($this->tokens);
		foreach ($arr_arrays as $array) {
			if ($this->check_array_is_may_weekdays($array)) {
				return true;
			}

			if ($this->check_array_is_may_months($array)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * возвращает массив переменных, которые участвуют в операциях вместе с ф-ями даты/времени
	 * @return array
	 */
	private function extract_variables_near_date()
	{
		$variables = array();
		foreach ($this->code_lines as $line) {
			$line_tokens = \Tokenizer::get_tokens_of_expression($line);
			// ищем выражения равенства
			$equal_operator_position = \Tokenizer::token_ispos($line_tokens, '=');
			if ($equal_operator_position !== false) {
				$is_date_fn_exist = false;
				$right_expression_part = array_slice($line_tokens, $equal_operator_position); // получаем выражение справа от равенства
				// ищем функции, работающие с датой/временем
				foreach ($this->suspicious_date_functions as $fn_name) {
					$is_date_fn_exist = \Tokenizer::token_ispos($right_expression_part, $fn_name, 'T_STRING');
					if ($is_date_fn_exist) {
						break;
					}
				}

				// извлекаем переменные слева, от знака равенства
				if ($is_date_fn_exist) {
					$left_expression_part = array_slice($line_tokens, 0, $equal_operator_position);
					$variables = array_merge($variables, \Variables::get_all_vars_in_expression($left_expression_part, true));
				}
			}
		}

		return array_unique($variables);
	}

	/**
	 * возвращает массив переменных, которые участвуют в строковых операциях
	 * @return array
	 */
	private function extract_variables_near_str_operations()
	{
		$variables = array();

		foreach ($this->code_lines as $line) {
			$line_tokens = \Tokenizer::get_tokens_of_expression($line);
			$is_str_fn_exist= false;
			foreach ($this->suspicious_string_functions as $fn_name) {
				$is_str_fn_exist = \Tokenizer::token_ispos($line_tokens, $fn_name, 'T_STRING');
				if ($is_str_fn_exist) {
					break;
				}
			}

			// ищем оператор конкатенации
			$is_str_fn_exist = $is_str_fn_exist || \Tokenizer::token_ispos($line_tokens, '.');

			if ($is_str_fn_exist) {
				$variables = array_merge($variables, \Variables::get_all_vars_in_expression($line_tokens, true));
			}
		}

		return array_unique($variables);
	}

	/**
	 * связи между переменными, когда переменные входят в одно выражение
	 * @param array $variables
	 * @return array
	 */
	private function extract_variables_near_variables(array $variables)
	{
		$rel_variables = array();
		foreach ($this->code_lines as $line) {
			$line_tokens = \Tokenizer::get_tokens_of_expression($line);
			foreach ($variables as $var_name) {
				if (\Tokenizer::token_ispos($line_tokens, $var_name, 'T_VARIABLE')) {
					$rel_variables = array_merge($rel_variables, \Variables::get_all_vars_in_expression($line_tokens, true));
				}
			}
		}

		$rel_variables = array_unique($rel_variables);
		foreach ($rel_variables as $i => $var_name) {
			if (in_array($var_name, $variables)) {
				unset($rel_variables[$i]);
			}
		}

		return array_values($rel_variables);
	}

	/**
	 * "насыщенность" кода извлеченными переменными
	 * @param array
	 * @return float
	 */
	private function code_saturation(array $all_variables)
	{
		$line_cnt = count($this->code_lines);

		$var_cnt = 0;
		foreach ($this->code_lines as $line) {

			$line_tokens = \Tokenizer::get_tokens_of_expression($line);


			$is_exist = false;

			foreach ($all_variables as $var_name) {
				$is_exist = \Tokenizer::token_ispos($line_tokens, $var_name, 'T_VARIABLE');
				if ($is_exist) {
					break;
				}
			}

			if ($is_exist) {
				$var_cnt++;
			}
		}

		return $var_cnt/$line_cnt;
	}
}