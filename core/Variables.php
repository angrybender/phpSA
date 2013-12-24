<?php
/**
 *
 * @author k.vagin
 */

class Variables {
	/**
	 * выводит массив имен переменных внутри выражения без повторов
	 * @param string|array $expression
	 * @param bool	вместо массива возвращать его название и не пропускать массивы
	 * @return array
	 */
	public static function get_all_vars_in_expression($expression, $array_as_var = false)
	{
		$result = array();
		if (!is_array($expression)) {
			if (!Tokenizer::is_open_tag($expression)) {
				$expression = '<?php ' . $expression;
			}

			$tokens = Tokenizer::get_tokens($expression);
		}
		else {
			$tokens = $expression;
		}

		foreach ($tokens as $i => $token) {
			if (is_array($token) && $token[0] === 'T_VARIABLE') {

				if (// $sometime-> это будет выражение
					isset($tokens[$i+1])
					&& is_array($tokens[$i+1])
					&& $tokens[$i+1][0] === 'T_OBJECT_OPERATOR'
				) {
					continue;
				}

				if (// $sometime:: это будет выражение
					isset($tokens[$i+1])
					&& is_array($tokens[$i+1])
					&& $tokens[$i+1][0] === 'T_DOUBLE_COLON'
				) {
					continue;
				}

				if (// $sometime(...) это будет выражение
					isset($tokens[$i+1])
					&& !is_array($tokens[$i+1])
					&& $tokens[$i+1] === '('
				) {
					continue;
				}

				if (// $sometime[ это будет выражение
					isset($tokens[$i+1])
					&& $tokens[$i+1] === '['
					&& !$array_as_var
				) {
					continue;
				}

				$result[] = $token[1];
			}
		}

		return array_unique($result);
	}

	/**
	 * возвращает информацию о найденных определениях массивов в коде
	 * на данный момент вложенные игнорирует
	 * @param string|array $code
	 * @return array
	 */
	public static function get_all_arrays($code)
	{
		if (!is_array($code)) {
			if (!Tokenizer::is_open_tag($code)) {
				$code = '<?php ' . $code;
			}

			$tokens = Tokenizer::get_tokens($code, true);
		}
		else {
			$tokens = $code;
		}

		$arr_tokens = array();
		$queue = 0;
		$br_queue = 0;
		$arrays_cnt = 0;

		foreach ($tokens as $i => $token) {
			if (is_array($token) && $token[0] === 'T_ARRAY' && $queue === 0) {
				$br_queue = 0;
				$queue++;
				$arrays_cnt++;
				$arr_tokens[$arrays_cnt] = array(
					'tokens' => array(), // определение
					'size' => 1 //  кол-во элементов
				);

				$tokens[$i+1] = null;
				continue;
			}

			if ($token === ';') {
				// очередной массив кончился
				$queue = 0;
			}

			// баланс скобок для внутренних массивов и выражений
			if ($queue === 1 && $token === '(') {
				$br_queue++;
			}

			if ($queue === 1 && $token === ')') {
				$br_queue--;
			}

			if ($queue === 1 && $token === ',' && $br_queue === 1) {
				// считаем кол-во элементов
				$arr_tokens[$arrays_cnt]['size']++;
			}
			elseif ($queue === 1) {
				// заносим очерендой элемент
				$arr_tokens[$arrays_cnt]['tokens'][] = $token;
			}
		}

		return $arr_tokens;
	}
} 