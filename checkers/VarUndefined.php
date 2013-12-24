<?php
/**
 * неопределенные переменные внутри процедур и ф-ий
 * сложные переменные пропускает
 *
 * todo captcha_helper.php line 44
 * @author k.vagin
 */

namespace Checkers;


class VarUndefined extends \Analisator\ParentChecker
{
	protected $types = array(
		CHECKER_ERRORS
	);

	protected $error_message = 'Неопределенная переменная';

	protected $extractor = 'Full'; // класс-извлекатель нужных блоков

	protected $is_line_return = true; // по умолчанию, строка ошибки определяется по началу блока, но функция проверки  может ее переопределить
	protected $line = array();

	private $tokens;

	private $predefined_vars = array(
		'$_POST',
		'$_SERVER',
		'$_GET',
		'$_POST',
		'$_FILES',
		'$_REQUEST',
		'$_SESSION',
		'$_ENV',
		'$_COOKIE',
		'$php_errormsg',
		'$HTTP_RAW_POST_DATA',
		'$http_response_header',
		'$argc',
		'$argv',
		'$this'
	);

	private $includes = array(
		'T_INCLUDE',
		'T_INCLUDE_ONCE',
		'T_REQUIRE',
		'T_REQUIRE_ONCE'
	);

	// такие функции, которые некоторый результат пишут в переданную им переменную
	private $function_callback_into_variable = array(
		'exec' => array(2,3), // возвращает значение в 2 и 3й аргументы
		'preg_match' => array(3),
		'preg_match_all' => array(3),
		'fsockopen' => array(3,4),
		'sqlite_open' => array(3),
		'sqlite_popen' => array(3),
		'preg_replace' => array(5)
	);

	public function check($tokens)
	{
		$this->tokens = $tokens;
		// нам нужно извлечь переданные переменные, поэтому стандартный извлекатель не подходит
		$procedures = array();
		foreach ($tokens as $i => $token) {
			if (is_array($token)
				&& $token[0] === 'T_FUNCTION'
				&& isset($tokens[$i+1])
				&& is_array($tokens[$i+1])
				&& $tokens[$i+1][0] == 'T_STRING'
			) {
				$open_block_position = \Tokenizer::token_ispos(array_slice($tokens, $i), '{');

				if ($open_block_position !== false) {
					$function_declaration = array_slice($tokens, $i+2, $open_block_position-2);
					$body = \Tokenizer::find_full_first_expression(array_slice($tokens, $i+$open_block_position), '{', '}', true);

					$procedures[] = array(
						'declaration' => $function_declaration,
						'body' => $body
					);
				}
			}
		}

		foreach ($procedures as $procedure) {
			$this->analize_code($procedure);
		}

		return empty($this->line);
	}

	/**
	 * анализирует отдельный блок кода
	 * ошибки добавляет сама
	 * todo refactor
	 *
	 * @param $tokens
	 * @return bool
	 */
	private function analize_code($tokens)
	{
		// если в теле функции есть вызов extract( - то тут ничего нельзя сказать, неопределенное поведение
		if (\Tokenizer::token_find($tokens['body'], \Tokenizer::get_tokens_of_expression('extract(')) !== false) {
			return true;
		}

		// если в теле функции происходил инклуд - точно так же неопределенное поведение, т.к. внутри загружаемого файла могут инициализироваться переменные
		// todo научиться раскрывать include
		foreach ($this->includes as $sign) {
			if (\Tokenizer::token_ispos($tokens['body'], false, $sign) !== false) {
				return true;
			}
		}

		$_args = \Variables::get_all_vars_in_expression($tokens['declaration']);

		$variables = \Variables::get_all_vars_in_expression($tokens['body']);

		// пропускаем все, которые входят в ф-ию как параметры
		$variables = array_diff($variables, $_args);

		// пропускаем предопределенные
		$variables = array_diff($variables, $this->predefined_vars);

		// пропускаем переменные, которые получают значение от функций, будучи переданными как аргумент. пример: preg_match($regexp, $mime, $matches)
		$callback_into_variable = array();
		foreach ($this->function_callback_into_variable as $func_name => $arr_func_arg_pos) {
			$_tokens = $tokens['body'];
			while (true) {
				$function_callback_into_variable_pos = \Tokenizer::token_ispos($_tokens, $func_name, 'T_STRING');
				if ($function_callback_into_variable_pos === false) {
					break;
				}

				$_tokens = array_slice($_tokens, $function_callback_into_variable_pos+1);
				$expression = \Tokenizer::find_full_first_expression($_tokens,'(', ')', true);
				unset($expression[0]); // первая скобка

				foreach ($arr_func_arg_pos as $func_arg_pos) {
					$var_name = $this->extract_need_arg($expression, $func_arg_pos);

					if (!empty($var_name)) {
						$callback_into_variable[] = $var_name;
					}
				}
			}
		}
		$callback_into_variable = array_unique($callback_into_variable);
		$variables = array_diff($variables, $callback_into_variable);

		// пропускаем переданные по ссылке
		foreach ($variables as $i => $var_name) {
			if (\Tokenizer::token_find($tokens['body'], \Tokenizer::get_tokens_of_expression('&'.$var_name)) !== false) {
				unset($variables[$i]);
			}
		}

		// пропускаем все, которые стоят слева от знака равенства
		$var_line = array(); // для ошибок пригодится позиция переменной
		$var_pos_cache = array(); // кэш позиций переменных
		$tokens_cnt = count($tokens['body']);
		foreach ($variables as $i => $var_name) {
			$var_pos = \Tokenizer::token_find($tokens['body'], \Tokenizer::get_tokens_of_expression($var_name));
			$var_pos_cache[$var_name] = $var_pos;
			$var_line[$var_name] = $tokens['body'][$var_pos][2];

			if ($var_pos < $tokens_cnt && $tokens['body'][$var_pos+1] === '=') {
				// если следующий символ - равно
				unset($variables[$i]);
			}
		}

		// пропускаем все, которые определяются декларацией цикла
		foreach ($variables as $i => $var_name) {
			$var_pos = $var_pos_cache[$var_name];

			if ($var_pos > 1
				&& is_array($tokens['body'][$var_pos-1])
				&& $tokens['body'][$var_pos-1][0] === 'T_AS'
			) {
				unset($variables[$i]);
			}
			elseif ($var_pos > 1
				&& is_array($tokens['body'][$var_pos-1])
				&& $tokens['body'][$var_pos-1][0] === 'T_DOUBLE_ARROW'
			) {
				// проверяем есть ли дальше переменная ,as и foreach:
				$is_var_exist = false;
				$is_as_exist = false;
				$is_foreach_exist = false;

				for ($j = $var_pos; $j>=0; $j--) {
					if ($tokens['body'][$j] === ';'
						|| $tokens['body'][$j] === '}'
						|| $tokens['body'][$j] === '{'
					) {
						break;
					}

					if (is_array($tokens['body'][$j])
						&& $tokens['body'][$j][0] === 'T_VARIABLE'
					){
						$is_var_exist = true;
					}

					if (is_array($tokens['body'][$j])
						&& $tokens['body'][$j][0] === 'T_AS'
					){
						$is_as_exist = true;
					}

					if (is_array($tokens['body'][$j])
						&& $tokens['body'][$j][0] === 'T_FOREACH'
					){
						$is_foreach_exist = true;
					}
				}

				if ($is_foreach_exist && $is_as_exist && $is_var_exist) {
					unset($variables[$i]);
				}
			}
		}

		// пропускаем все, которые глобальные или statis:
		$_tokens = $tokens['body'];
		$global_vars = array();
		while (true) {
			$global_def_pos = \Tokenizer::token_ispos($_tokens, false, 'T_GLOBAL');
			if ($global_def_pos === false) {
				$global_def_pos = \Tokenizer::token_ispos($_tokens, false, 'T_STATIC');
			}

			if ($global_def_pos === false) {
				break;
			}

			$_tokens = array_slice($_tokens, $global_def_pos+1);

			foreach ($_tokens as $i => $token) {
				if (is_array($token) && $token[0] === 'T_VARIABLE') {
					$global_vars[] = $token[1];
					continue;
				}

				if ($token === ',') {
					continue;
				}

				break; // вот такая загогулина
			}
		}
		$variables = array_diff($variables, $global_vars);

		// пропускаем все, которые внутри unset (лишние ложные срабатывания)
		$_tokens = $tokens['body'];
		$unset_vars = array();
		while (true) {
			$unset_pos = \Tokenizer::token_ispos($_tokens, false, 'T_UNSET');

			if ($unset_pos === false) {
				break;
			}

			$_tokens = array_slice($_tokens, $global_def_pos+1);

			foreach ($_tokens as $i => $token) {
				if (is_array($token) && $token[0] === 'T_VARIABLE') {
					$unset_vars[] = $token[1];
				}

				if ($token === ')' || $token === ';') {
					break;
				}
			}
		}
		$variables = array_diff($variables, $unset_vars);

		// пропускаем все, которые внутри catch()
		$_tokens = $tokens['body'];
		$catch_vars = array();
		while (true) {
			$catch_pos = \Tokenizer::token_ispos($_tokens, false, 'T_CATCH');

			if ($catch_pos === false) {
				break;
			}

			$_tokens = array_slice($_tokens, $catch_pos+1);

			foreach ($_tokens as $i => $token) {
				if (is_array($token) && $token[0] === 'T_VARIABLE') {
					$catch_vars[] = $token[1];
				}

				if ($token === ')' || $token === '{') {
					break;
				}
			}
		}
		$variables = array_diff($variables, $catch_vars);

		// игнорируем переменные, сразу инициализированные как массивы:
		foreach ($variables as $i => $var_name) {
			if (\Tokenizer::token_find($tokens['body'], \Tokenizer::get_tokens_of_expression($var_name.'[')) !== false) {
				unset($variables[$i]);
			}
		}

		// игнорируем внутри list()
		$_tokens = $tokens['body'];
		$list_vars = array();
		while (true) {
			$list_pos = \Tokenizer::token_ispos($_tokens, false, 'T_LIST');
			if ($list_pos === false) {
				break;
			}

			$_tokens = array_slice($_tokens, $list_pos+1);

			$expression = \Tokenizer::find_full_first_expression($_tokens, '(', ')', true);
			$list_vars = array_merge($list_vars, \Variables::get_all_vars_in_expression($expression));
		}
		$list_vars = array_unique($list_vars);
		$variables = array_diff($variables, $list_vars);

		if (!empty($variables)) {
			//print_r($variables);
			//die();
			foreach ($variables as $var_name) {
				$this->line[] = $var_line[$var_name];
			}
		}
	}

	/**
	 * извлекает нужные аргумент из выражения
	 * @param array
	 * @param int
	 * @return array
	 */
	private function extract_need_arg(array $tokens, $pos = 0)
	{
		// убиваем все, что внутри вложенных скобок (если есть), чтобы запятые посчитать верно
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

			if ($comma_cnt === $pos - 1) {
				$result_tokens = array_slice($tokens, $i+1);
				break;
			}
		}

		return empty($result_tokens) ? '' : $result_tokens[0][1];
	}
} 