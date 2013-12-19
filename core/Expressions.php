<?php
/**
 *
 * @author k.vagin
 */

class Expressions {
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
		if (!Tokenizer::is_open_tag($expression)) {
			$expression = '<?php ' . $expression;
		}

		$tokens = Tokenizer::get_tokens($expression, true);
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
		$exist_vars = Variables::get_all_vars_in_expression($expression);
		foreach ($exist_vars as $var) {
			$var_count++;
			$tokens = Tokenizer::token_replace('T_VARIABLE', $var, $var_template.$var_count, $tokens);
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

		return Tokenizer::remove_open_tag(Tokenizer::tokens_to_source($tokens));
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