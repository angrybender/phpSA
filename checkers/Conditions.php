<?php
/**
 *
 * @author k.vagin
 */

class Conditions {

	public static function check($expression="")
	{
		return self::check_boolean_expression($expression);
	}

	/**
	 * проверяет булево выражение на ошибки связанные с возможным копипастом
	 * принцип: проходим по всем переменным и заменяем их на TRUE или FALSE (без учета повторов) - потом считаем по каждой переменной сколько раз значение выражения не изменилось
	 * если повторов переменных нет, то выражение чистое
	 *
	 * внимание: выражение должно быть обязательно нормализовано (Tokenizer::reduce_and_normalize_boolean_expression)
	 *
	 * @param string $expression
	 * @return bool
	 */
	public static function check_boolean_expression($expression="")
	{
		$tokens = Tokenizer::get_tokens('<?php ' . $expression, true);

		$vars = Tokenizer::get_all_vars_in_expression($expression);
		if (count($vars)<2) return true; // нечего проверять

		$var_count = count($vars); // число разных переменных
		$var_inner_count = 0; // общее число вхождений переменных в т.ч. одной и той же переменной

		foreach ($tokens as $token) {
			if (is_array($token) && $token[0] == 'T_VARIABLE') {
				$var_inner_count++;
			}
		}

		if ($var_inner_count === $var_count) {
			return true;
		}

		$expression_normal_result = // хеш значений функции
		$variables = Tokenizer::get_all_variables($var_count); // карта входных значений

		$expression_normal_result = self::calculate_boolean($expression, $vars, $variables);

		$suspicion = array();
		foreach ($tokens as $i => $token) {
			if (is_array($token) && $token[0] == 'T_VARIABLE') {
				$tokens[$i] = array(
					'T_STRING',
					'TRUE',
					1
				);

				$new_expression = substr(Tokenizer::tokens_to_source($tokens), 5); // тут это допустимо, структура детерминирована
				$new_exp_results1 = self::calculate_boolean($new_expression, $vars, $variables);

				//echo $new_expression . ' : ' . $new_exp_results1 . ' / ' . $expression_normal_result . PHP_EOL;

				$tokens[$i] = array(
					'T_STRING',
					'FALSE',
					1
				);

				$new_expression = substr(Tokenizer::tokens_to_source($tokens), 5);
				$new_exp_results2 = self::calculate_boolean($new_expression, $vars, $variables);

				//echo $new_expression . ' : ' . $new_exp_results2 . ' / ' . $expression_normal_result. PHP_EOL;

				if ($new_exp_results1 === $expression_normal_result || $new_exp_results2 === $expression_normal_result) {
					$suspicion[$token[1]] = isset($suspicion[$token[1]]) ? $suspicion[$token[1]]+1 : 1;
				}

				$tokens[$i] = $token; // не забудем восстановить как было
			}
		}

		foreach ($suspicion as $val) {
			if ($val > 1) return false;
		}

		return true;
	}

	/**
	 * @param string $expression
	 * @param array $vars
	 * @param array $variables
	 * @return string
	 */
	private static function calculate_boolean($expression = "", array $vars, array $variables)
	{
		$expression_value_map = array();
		foreach ($variables as $nest) {
			$in_var = array_combine(
				array_map(function($value){
					return substr($value, 1);
				}, $vars),
				array_map(function($value){
					return $value == 1;
				}, $nest)
			);

			$expression_value_map[] = Tokenizer::calculate_boolean_expression($expression, $in_var);
		}

		return join('', array_map(function($value) {
							return $value ? '1' : '0';
						}, $expression_value_map));
	}
}