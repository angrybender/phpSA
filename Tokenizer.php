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
	public static function tokens_to_source(array $tokens)
	{
		$arr_tokens = array();
		foreach ($tokens as $token) {
			if (!is_array($token)) {
				$arr_tokens[] = $token;
			}
			else {
				$arr_tokens[] = $token[1];
			}
		}

		return join('', $arr_tokens);
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
				$expression = '<?php' . $expression;
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
	 * @return string
	 */
	public static function find_full_first_expression($tokens, $start, $end)
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
				$arr_tokens[] = $token[1];
			}
		}

		return join('', $arr_tokens);
	}

	/**
	 * выбирает выражения внутри if
	 * todo elseif
	 *
	 * @param $tokens
	 * @return array
	 */
	public static function get_all_ifconditions($tokens)
	{
		$result = array();

		$if_start = false;
		foreach ($tokens as $i => $token) {
			if ($token[0] == 'T_IF') {
				$result[] = self::find_full_first_expression(array_slice($tokens, $i+1), '(', ')');
			}
		}

		return $result;
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
	 * выводит массив имен переменных внутри выражения без повторов
	 * @param string $expression
	 * @return array
	 */
	public static function get_all_vars_in_expression($expression)
	{
		$result = array();
		if (!self::is_open_tag($expression)) {
			$expression = '<?php ' . $expression;
		}

		$tokens = self::get_tokens($expression);
		//print_r($tokens);

		foreach ($tokens as $i => $token) {
			if (is_array($token) && $token[0] == 'T_VARIABLE') {

				if (// $sometime-> это будет выражение
					isset($tokens[$i+1])
					&& is_array($tokens[$i+1])
					&& $tokens[$i+1][0] == 'T_OBJECT_OPERATOR'
				) {
					continue;
				}

				if (// $sometime:: это будет выражение
					isset($tokens[$i+1])
					&& is_array($tokens[$i+1])
					&& $tokens[$i+1][0] == 'T_DOUBLE_COLON'
				) {
					continue;
				}

				if (// $sometime(...) это будет выражение
					isset($tokens[$i+1])
					&& !is_array($tokens[$i+1])
					&& $tokens[$i+1] == '('
				) {
					continue;
				}

				if (// $sometime[ это будет выражение
					isset($tokens[$i+1])
					&& !is_array($tokens[$i+1])
					&& $tokens[$i+1] == '['
				) {
					continue;
				}

				$result[] = $token[1];
			}
		}

		return array_unique($result);
	}

	/**
	 * упрощает переданное выражение, заменяя все подвыражения на переменные, сводя
	 * исходное выражение к булевской функции
	 * нет обработки цепочечных вызовов, выражений вида "{$a}"
	 *
	 * @param string $expression
	 * @return string
	 */
	public static function reduce_and_normalize_boolean_expression($expression)
	{
		if (!self::is_open_tag($expression)) {
			$expression = '<?php ' . $expression;
		}

		$tokens = self::get_tokens($expression, true);
		$dict = array(); // словарь замен
		$var_count = 0; // псевдопеременные
		$var_template = '$rvar_';

		/*
		 * заменяем
		 * вызовы функций date(), microtime(true), is_array($arr)
		 * вызовы методов классов $this->, $class->, class::, $some::
		 * обращения к проперти классов
		 * унарные операции и инструкции языка
		 * все подвыражения, которые не содержат логических операторов 1+$a, ($b+$c)/$d, $f == 2, $some + $delta >= 8
		 */

		//  заменим все переменные на синтетические имена
		$exist_vars = self::get_all_vars_in_expression($expression);
		foreach ($exist_vars as $var) {
			$var_count++;
			$tokens = self::token_replace('T_VARIABLE', $var, $var_template.$var_count, $tokens);
		}

		// заменим любую фунцию и ряд инструкций языка на синтетическую переменную
		// но, так, чтобы одинаковые фукнции с одним набором аргументов прервратились в 1 переменную
		$is_func_start = false;
		$func_signature='';
		$func_start = -1;
		$brackets_balance=0;
		$func_dict=array();
		$func_equal_instructions = array(
			'T_STRING',
			'T_VARIABLE',
			'T_LIST',
			'T_ISSET',
			'T_EVAL',
			'T_EMPTY'
		);
		foreach ($tokens as $i => $token) {
			if ( // ищем старт функции
				!$is_func_start
				&& is_array($token)
				&& in_array($token[0], $func_equal_instructions)
				&& isset($tokens[$i+1])
				&& $tokens[$i+1] === '('
			) {
				$is_func_start = true;
				$brackets_balance = 0;
				$func_signature = strtolower($token[1]);
				$func_start = $i;
				$tokens[$i] = '';
				continue;
			}

			if ($is_func_start && $token === '(') {
				$brackets_balance++;
				$tokens[$i] = '';
			}

			if ($is_func_start && $token === ')') {
				$brackets_balance--;
				$tokens[$i] = '';
			}

			if ($is_func_start && $brackets_balance == 0) {
				$is_func_start = false;

				if (!isset($func_dict[$func_signature])) {
					$var_count++;
					$var_name = $var_template.$var_count;
					$func_dict[$func_signature] = $var_name;
				}
				else {
					$var_name = $func_dict[$func_signature];
				}

				$tokens[$func_start] = array(
					'T_VARIABLE',
					$var_name,
					1
				);
			}
			elseif ($is_func_start && $brackets_balance>=1) {
				$func_signature = $func_signature . (is_array($token) ? $token[1] : $token);
				$tokens[$i] = '';
			}
		}

		// очистка от пустых токенов
		$tmp_result = array_filter($tokens);
		$tokens = array();
		foreach ($tmp_result as $token) {
			$tokens[] = $token;
		}

		// устранение ссылок на классы
		// не понимает цепочечных вызовов
		// одинаковые вызовы заменяются на 1 переменную (аналогично как с ф-ями)
		$classes_dict=array();
		foreach ($tokens as $i => $token) {
			if ( // ищем обращение к проперти класса (тут у нас уже ниодного метода не останется в выражении, они заменены на переменные кодом выше)
				is_array($token)
				&& ($token[0] === 'T_STRING' || $token[0] === 'T_VARIABLE')
				&& isset($tokens[$i+1])
				&& is_array($tokens[$i+1])
				&& ($tokens[$i+1][0] === 'T_OBJECT_OPERATOR' || $tokens[$i+1][0] === 'T_DOUBLE_COLON')
			) {
				$sign = strtolower($token[1]) . $tokens[$i+1][0] . strtolower($tokens[$i+2][1]);
				if (!isset($classes_dict[$sign])) {
					$var_count++;
					$var_name = $var_template.$var_count;
					$classes_dict[$sign] = $var_name;
				}
				else {
					$var_name = $classes_dict[$sign];
				}

				$tokens[$i] = array(
					'T_VARIABLE',
					$var_name,
					1
				);

				$tokens[$i+1] = ''; // оператор
				$tokens[$i+2] = ''; // переменная
			}
		}

		// очистка от пустых токенов
		$tmp_result = array_filter($tokens);
		$tokens = array();
		foreach ($tmp_result as $token) {
			$tokens[] = $token;
		}

		// очистка от всех не булевский операторов (оператор ! придется обрабатывать костылем)
		$unar_operators = array(
			'T_ARRAY_CAST',
			'T_BOOL_CAST',
			'T_DEC',
			'T_DOUBLE_CAST',
			'T_INC',
			'T_INT_CAST',
			'T_NEW',
			'T_OBJECT_CAST',
			'T_STRING_CAST',
			'T_UNSET_CAST'
		);
		$operator_args = array( // участники операций
			'T_VARIABLE',
			'T_CONSTANT_ENCAPSED_STRING',
			'T_LNUMBER',
			'T_DNUMBER'
		);
		$binar_operators = array( // бинарные операторы,подлежащие замене
			'T_SL',
			'T_SR',
			'T_IS_SMALLER_OR_EQUAL',
			'T_IS_NOT_EQUAL',
			'T_IS_GREATER_OR_EQUAL',
			'T_CONCAT_EQUAL',
			'T_IS_EQUAL',
			'T_IS_IDENTICAL'
		);
		$while_stop_semafor=1000;
		while (true) {
			$while_stop_semafor--; // защита от зацикливания
			if ($while_stop_semafor==0) {
				die('reduce_and_normalize_boolean_expression while overload');
			}
			$is_found=false;
			foreach ($tokens as $i => $token) {
				if (is_array($token) // что то между двумя переменными(числами, строками, др. значениями) считаем за оператор, или если он в списке $binar_operators
					&& in_array($token[0], $operator_args)
					&& isset($tokens[$i+1])
					&& (!is_array($tokens[$i+1]) || in_array($tokens[$i+1][0], $binar_operators))
					&& isset($tokens[$i+2])
					&& is_array($tokens[$i+2])
					&& in_array($tokens[$i+2][0], $operator_args)
				) {
					$is_found = true;
					$var_count++;
					$var_name = $var_template.$var_count;

					$tokens[$i] = array(
						'T_VARIABLE',
						$var_name,
						1
					);

					$tokens[$i+1] = ''; // оператор
					$tokens[$i+2] = ''; // вторая переменная

					break;
				}
			}

			// очистка от выражений вида ($var) (1)
			foreach ($tokens as $i => $token) {
				if ($i>0
					&& is_array($token) // что то между двумя переменными считаем за оператор
					&& in_array($token[0], $operator_args)
					&& $tokens[$i-1] === '('
					&& isset($tokens[$i+1])
					&& $tokens[$i+1] === ')'
				) {
					$tokens[$i-1] = ''; // (
					$tokens[$i+1] = ''; // )

					$is_found = true;
				}
			}

			// очистка от унарных операторов и инструкций приведения типов
			foreach ($tokens as $i => $token) {
				if (is_array($token)
					&& in_array($token[0], $unar_operators)
				) {
					$tokens[$i] = '';
					$is_found = true;
				}
			}

			if (!$is_found) break;

			// очистка от пустых токенов и ошметков от массивов
			$tmp_result = array_filter($tokens);
			$tokens = array();
			foreach ($tmp_result as $token) {
				if ($token !== ']') $tokens[] = $token;
			}
		}

		return self::remove_open_tag(self::tokens_to_source($tokens));
	}


	/**
	 * получить массив всех возможных булевских значений для N переменных (их ,как известно 2^N)
	 * @param int $count
	 * @return array
	 */
	public static function get_all_variables($count=0)
	{
		if ($count>=10) {
			die('FUCK' . PHP_EOL); // wtf
		}

		$variants = pow(2, $count);
		$result = array();
		$register = array_fill(0, $count, 0);

		for ($i=0; $i<$variants; $i++) {
			$result[] = $register;

			$register[$count-1] = 1 - $register[$count-1];
			for ($j = $count-1; $j>0; $j--) {
				if ($register[$j]==0) { // перенос разряда
					$register[$j-1] = 1 - $register[$j-1];
				}
				else {
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * @param string $expression	выражение в строке
	 * @param array $values			значения переменных в ассоциативном массиве
	 * @return bool
	 */
	public static function calculate_boolean_expression($expression="",array $values)
	{
		if (substr($expression,-1,1) !== ';') {
			$expression = $expression.';';
		}

		$vars_declare = "";
		foreach ($values as $name => $value) {
			$vars_declare = $vars_declare .
				'$'.$name.' = ' . ($value ? 'TRUE' : 'FALSE') . ';'. PHP_EOL;
		}

		return eval($vars_declare . 'return ' . $expression);
	}

} 