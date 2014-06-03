<?php
/**
 *
 * @author k.vagin
 */

class Tokenizer {

	/**
	 * возвращает токены
	 * @param string $source
	 * @param bool $is_comment_reduce - k.o.
	 * @return array
	 */
	public static function get_tokens($source, $is_comment_reduce=false)
	{
		$tokens = token_get_all($source);

		$ignore = array(
			'T_WHITESPACE'
		);

		if ($is_comment_reduce) {
			$ignore = array_merge($ignore, array(
				'T_COMMENT',
				'T_DOC_COMMENT'
			));
		}

		$tokens = array_map(function($token) use ($ignore) {
			if (is_array($token)) {
				$token[0] = token_name($token[0]);

				if (in_array($token[0], $ignore)) {
					return false;
				}

				$token[1] = trim($token[1]);
			} else {
				$token = trim($token);
			}

			return $token;
		}, $tokens);

		$tmp_result = array_filter($tokens);
		// перенормировка todo для этого есть ф-я в языке
		$result = array();

		foreach ($tmp_result as $item) {
			$result[] = $item;
		}

		return $result;
	}

	/**
	 * возвращает токены, но давит комментарии и пустые строки + сам начальных тэг ставит
	 * @param string $code
	 * @return array
	 */
	public static function get_tokens_of_expression($code)
	{
		$is_remove_open_tag = false;
		if (!is_array($code)) {
			if (!self::is_open_tag($code)) {
				$is_remove_open_tag = true;
				$code = '<?php ' . $code;
			}
		}

		$result = self::get_tokens($code, true);
		if ($is_remove_open_tag) {
			unset($result[0]);
			$result = array_values($result);
		}

		return $result;
	}

	/**
	 * нормализирует код
	 * @param string $source
	 * @return string
	 */
	public static function code_normalizer($source = "")
	{
		$tokens = self::get_tokens($source, true);

		$arr_tokens = array();
		foreach ($tokens as $token) {
			if (!is_array($token)) {
				$arr_tokens[] = trim($token);
			}
			else {
				$arr_tokens[] = trim($token[1]);
			}
		}

		return join('', $arr_tokens);
	}

	/**
	 * токены в строку. без нормализации
	 * @param array $tokens
	 * @return string
	 */
	public static function tokens_to_source(array $tokens, $not_beautiful = false)
	{
		$arr_tokens = array();
		foreach ($tokens as $token) {
			if (!is_array($token)) {
				$arr_tokens[] = $token;
			}
			else {
				// иначе склеиваются
				if (strtoupper($token[1]) === 'OR' || strtoupper($token[1]) === 'AND') {
					$token[1] = " {$token[1]} ";
				}
				$arr_tokens[] = $token[1];
			}
		}

		return join($not_beautiful ? ' ' : '', $arr_tokens);
	}

	/**
	 * заменяет токен (подобно str_ireplace)
	 * если на входе массив токенов - тоже и на выходе
	 * если на входе строка, тоже и на выходе
	 *
	 * внимание, может похерить open tag( <? который если он не в начале)
	 *
	 * @param string $type
	 * @param mixed $value
	 * @param mixed $new_value
	 * @param mixed $expression
	 * @return mixed
	 */
	public static function token_replace($type, $value, $new_value, $expression)
	{
		$is_source = false;
		$is_open_tag = false; // был или нет открытый тэг
		if (!is_array($expression)) {
			$is_source = true;
			if (!self::is_open_tag($expression)) {
				$expression = '<?php ' . $expression;
			} else {
				$is_open_tag = true;
			}

			$expression = token_get_all($expression);
		}

		foreach ($expression as $i => $token) {
			if (is_array($token)) {

				$token_name = $is_source ? token_name($token[0]) : $token[0];
				if ($type == $token_name
					&& strtolower($value) === trim(strtolower($token[1]))
				) {
					$expression[$i][1] = $new_value;
				}

			}
		}

		if ($is_source) {
			$result = array();
			foreach ($expression as $token) {
				$result[] = is_array($token) ? $token[1] : $token;
			}

			$expression = join('', $result);
			if (!$is_open_tag) {
				$expression = self::remove_open_tag($expression);
			}
		}

		return $expression;
	}

	/**
	 * удалет открытие пхп тэга
	 * не нормализует!
	 *
	 * @param string $source
	 * @return string
	 */
	public static function remove_open_tag($source="")
	{
		$tokens = token_get_all($source);

		foreach ($tokens as $i => $token) {
			if (is_array($token)
				&& token_name($token[0]) == 'T_OPEN_TAG'
				&& isset($tokens[$i+1])
				&& strtolower($tokens[$i+1][1]) == 'php'
			) {
				$tokens[$i+1] = false;
				$tokens[$i] = false;
			}
		}

		$tmp_result = array_filter($tokens);

		// перенормировка
		$result = array();
		foreach ($tmp_result as $item) {
			$result[] = is_array($item) ? $item[1] : $item;
		}

		return join('', $result);
	}

	/**
	 * выбирает все,что между $start и $end, включая $start и $end
	 * поддерживает вложенность
	 *
	 * @param $tokens
	 * @param $start
	 * @param $end
	 * @param $return_array
	 * @return string
	 */
	public static function find_full_first_expression(array $tokens, $start, $end, $return_array = false)
	{
		$arr_tokens = array();
		$queue = 0;

		$ignore = array(
			'T_COMMENT',
			'T_DOC_COMMENT'
		);

		foreach ($tokens as $token) {
			if (!is_array($token)) {
				if ($token == $start) $queue++;
				$arr_tokens[] = $token;
				if ($token == $end) $queue--;
				if ($queue==0) break;
			}
			elseif (!in_array($token[0], $ignore)) {
				$arr_tokens[] = $return_array ? $token : $token[1];
			}
		}

		if ($return_array) {
			return $arr_tokens;
		}

		return join('', $arr_tokens);
	}

	/**
	 * есть ли в переданном коде токен открытия php кода
	 * @param string $code
	 * @return bool
	 */
	public static function is_open_tag($code)
	{
		$tokens = self::get_tokens($code);
		foreach ($tokens as $token) {
			if (is_array($token) && $token[0] == 'T_OPEN_TAG') {
				return true;
			}
		}

		return false;
	}

	/**
	 * грубо разбить код по строкам (по ; и { })
	 * @param string|array $code
	 * @return array
	 */
	public static function format_code_into_lines($code)
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

		$tokens[0] = false;

		$lines = array();
		$line = array();

		foreach ($tokens as $i => $token) {

			if ($token === '{' || $token === '}') {
				$lines[] = self::tokens_to_source($line);
				$line = array();
			}

			if ($token !== ';') {
				$line[] = $token;
			}

			if ($token === ';' || $token === '{' || $token === '}') {
				$lines[] = self::tokens_to_source($line);
				$line = array();
			}
		}

		if (!empty($line)) {
			$lines[] = self::tokens_to_source($line);
		}

		return $lines;
	}


	/**
	 * подобно str_pos
	 * @param mixed $code
	 * @param $token_value 	может быть false, но тогда $token_type не долежн быть ложью
	 * @param $token_type	если ложь, то ищем атомарный токен
	 * @return int
	 */
	public static function token_ispos($code, $token_value, $token_type = false)
	{
		if (!is_array($code)) {
			if (!self::is_open_tag($code)) {
				$code = '<?php ' . $code;
			}

			$code = self::get_tokens($code, true);
		}

		foreach ($code as $i => $token) {

			if ($token_type === false && $token === $token_value) {
				return $i;
			}

			if ($token_value === false && is_array($token) && $token[0] === $token_type) {
				return $i;
			}

			if ($token_value !== false && $token_type !== false
				&& $token[0] === $token_type
				&& $token[1] == $token_value
			) {
				return $i;
			}
		}

		return false;
	}

	/**
	 * ищет в токенах сложное выражение, переданное как массив
	 * @param mixed $code
	 * @param array $needle
	 * @return int
	 */
	public static function token_find($code, array $needle)
	{
		if (!is_array($code)) {
			if (!self::is_open_tag($code)) {
				$code = '<?php ' . $code;
			}

			$code = self::get_tokens($code, true);
		}

		$needle_cnt = count($needle);
		foreach ($code as $i => $token) {
			$sub_array = array_slice($code, $i, $needle_cnt);
			if (count($sub_array) < $needle_cnt) break;

			$is_eq = true;
			foreach ($sub_array as $j => $sub_token) {
				if (!is_array($sub_token) && ($sub_token !== $needle[$j])) {
					$is_eq = false;
					break;
				}

				if (is_array($sub_token) &&
					(
						!is_array($needle[$j])
						||
						($sub_token[0] !== $needle[$j][0])
						||
						($sub_token[1] !== $needle[$j][1])
					)
				) {
					$is_eq = false;
					break;
				}
			}

			if ($is_eq) {
				return $i;
			}
		}

		return false;
	}


	/**
	 * возвращает полное имя переменной, которой присваивается результат работы функции,
	 * вызов которой расположен на $position_of_fn-й позиции
	 * @param $tokens
	 * @param int $position_of_fn
	 * @return string
	 */
	public static function get_assignment_variable_name(array $tokens, $position_of_fn)
	{
		if ($position_of_fn < 1) {
			return false;
		}

		if ($tokens[$position_of_fn][0] !== 'T_STRING') {
			return false;
		}

		$result_tokens = array();
		$is_eq_find = false;
		for ($i=$position_of_fn-1; $i>=0; $i--) {

			if ($tokens[$i] === ';'
				|| $tokens[$i] === '}'
				|| (is_array($tokens[$i]) && $tokens[$i][0] === 'T_OPEN_TAG')
				|| (is_array($tokens[$i]) && $tokens[$i][0] === 'T_COMMENT')
				|| (is_array($tokens[$i]) && $tokens[$i][0] === 'T_DOC_COMMENT')) {
				break;
			}

			if ($is_eq_find) {
				$result_tokens[] = $tokens[$i];
			}

			if (!$is_eq_find && $tokens[$i] === '=') {
				$is_eq_find = true;
			}
		}

		$result_tokens = array_reverse($result_tokens);
		return strtolower(self::tokens_to_source($result_tokens));
	}

	/**
	 * @param array $tokens
	 * @return array
	 */
	public static function remove_comments(array $tokens)
	{
		return self::remove_token_by_type($tokens, array('T_COMMENT', 'T_DOC_COMMENT'));
	}

	/**
	 * @param array $tokens
	 * @param array|string $exclude_tokens
	 * @return array
	 */
	public static function remove_token_by_type(array $tokens, $exclude_tokens)
	{
		if (is_scalar($exclude_tokens)) {
			foreach ($tokens as $i => $token) {
				if (is_array($token) && $token === $exclude_tokens) {
					unset($tokens[$i]);
				}
			}
		}
		elseif (count($exclude_tokens) === 2) {
			foreach ($tokens as $i => $token) {
				// надеюсь это оптимизация а не глюк
				if (is_array($token) && ($token[0] === $exclude_tokens[0] || $token[0] === $exclude_tokens[1])) {
					unset($tokens[$i]);
				}
			}
		}
		else {
			foreach ($tokens as $i => $token) {
				if (is_array($token) && in_array($token[0], $exclude_tokens)) {
					unset($tokens[$i]);
				}
			}
		}

		return array_values($tokens);
	}

	public static function tokens_is_eq($token1, $token2, $strong = false)
	{
		if (is_array($token1) && is_array($token2)) {
			$type_eq = ($token1[0] === $token2[0]);
			if  (!$strong) {
				return true;
			}

			return ($token1[1] === $token2[1]);
		}

		if (!is_array($token1) && !is_array($token2)) {
			return ($token1 === $token2);
		}

		return false;
	}

	/**
	 * типа str_ireplace($foo, ''); только может целые конструкции херить
	 *
	 * @param array $tokens
	 * @param array $exclude_tokens
	 * @return array
	 */
	public static function remove_token_by_token(array $tokens, array $exclude_tokens)
	{
		$replace_count = count($exclude_tokens);

		foreach ($tokens as $i => $token)
		{
			if (empty($token)) {
				continue;
			}

			$is_eq = true;
			foreach ($exclude_tokens as $j => $exclude_token) {
				$is_eq = $is_eq && self::tokens_is_eq($tokens[$i+$j], $exclude_token);
				if (!$is_eq) break;
			}

			if ($is_eq) {
				for ($k=$i; $k<=$replace_count; $k++) {
					$tokens[$k] = null;
				}
			}
		}

		return $tokens;
	}
} 