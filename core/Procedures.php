<?php
/**
 * работа с функциями, методами
 * @author k.vagin
 */

class Procedures {

	/**
	 * выводит список всех процедур / функций, вызываемых внутри переданного кода
	 * @param string|array $code
	 * @return array
	 */
	public static function get_all_procedures_in_code($code)
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

		$code = null;
		$results = array();

		$func_equal_instructions = array(
			'T_LIST',
			'T_ISSET',
			'T_EVAL',
			'T_EMPTY'
		);

		foreach ($tokens as $i => $token) {
			if (!is_array($token)) {
				continue;
			}

			if (in_array($token[0], $func_equal_instructions)) {
				$results[] = strtolower($token[1]);
				continue;
			}

			$is_add = false;
			if ($token[0] == 'T_STRING'
				&& isset($tokens[$i+1])
				&& $tokens[$i+1] === '('
			) {
				//либо токен в самом начале файла, либо перед ним нет никаких операторов работы с классами/объектами
				$is_add = true;
				if (isset($tokens[$i-1])
					&& is_array($tokens[$i-1])
					&& ($tokens[$i-1][0] == 'T_OBJECT_OPERATOR' || $tokens[$i-1][0] == 'T_DOUBLE_COLON' || $tokens[$i-1][0] == 'T_NEW')
				) {
					$is_add = false;
				}
			}

			$ext_ct = self::extract_full_class_method($tokens, $i);
			if (!empty($ext_ct)) {
				$results[] = $ext_ct;
				$is_add = false;
			}

			if ($is_add) {
				// тут очень хитр о - если токен подходит, но, с него экстрактор методов объектов не находит цепочку - добавляем
				$results[] = $token[1];
			}
		}

		return $results;
	}

	/**
	 * раскручивает цепочку вперед и возвращает всю конструктцию вызова метода целиком $this->getObj()::instance()
	 * некорректно обрабатывает конструкции вида $class->sub($class->param()) (вложенный метод)
	 *
	 * @param array $tokens
	 * @param int $token_position позиция, в котрой лежит первый член цепочки
	 * @return null|string
	 */
	public static function extract_full_class_method(array $tokens, $token_position)
	{
		$tokens = array_slice($tokens, $token_position);
		$result = array();

		$need_tokens = array(
			'T_STRING',
			'T_VARIABLE',
			'T_OBJECT_OPERATOR',
			'T_DOUBLE_COLON'
		);

		$is_open_bracket = false;
		$is_call_operator_found = false;
		foreach ($tokens as $i => $token) {
			if ($is_open_bracket
				&& $token === ')'
				&& (
					!isset($tokens[$i+1]) // либо конец кода
					||
					isset($tokens[$i+1]) && !is_array($tokens[$i+1]) // либо дальше не оператор -> ::
				)
			) {
				// нашли конец цепочки вызовов
				break;
			}

			if ($i==0 &&
				(
					!is_array($token)
					||
					is_array($token) && $token[0] !== 'T_STRING' && $token[0] !== 'T_VARIABLE'
				)
			) {
				// херню передали
				break;
			}

			if ($i>0 &&
				(
					!is_array($token) && $token !== '(' && $token !== ')'
					||
					is_array($token) && !in_array($token[0], $need_tokens)
				)
			) {
				// закончилось выражение
				break;
			}

			if ($token === '(' && !$is_open_bracket) {
				// признак того что мы вообще нашли тут метод
				$is_open_bracket = true;
			}

			if (is_array($token)
				&& ($token[0] == 'T_OBJECT_OPERATOR' || $token[0] == 'T_DOUBLE_COLON')
			) {
				$is_call_operator_found = true;
			}


			$result[] = $token;
		}

		if (!$is_open_bracket || !$is_call_operator_found) {
			return null;
		}


		for ($i = count($result) - 1; $i>=0; $i--) {
			if ($result[$i] !== '(') {
				unset($result[$i]);
			}
			else {
				unset($result[$i]);
				break;
			}
		}

		return Tokenizer::tokens_to_source(array_values($result));
	}
} 