<?php
/**
 * неопределенные переменные внутри процедур и ф-ий
 * сложные переменные пропускает
 *
 * todo captcha_helper.php line 44
 * todo когда вызываемая ф-ия принимает по ссылке (ложные срабатывания)
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
		'$GLOBALS',
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
		'preg_replace' => array(5),
		'openssl_sign' => array(2,3),
		'pcntl_waitpid' => array(2),
		'stream_socket_server' => array(2,3),
		'stream_socket_client' => array(2,3),
		'pcntl_wait' => array(1),
	);

	private $var_line = array(); // для ошибок пригодится позиция переменной
	private $var_pos_cache = array(); // кэш позиций переменных

	public function check($tokens)
	{
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
						'body' => $body,
						'name' => $tokens[$i+1][1]
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
		if ($this->is_undef_behavior($tokens)) {
			return true;
		}

		$_args = \Variables::get_all_vars_in_expression($tokens['declaration']);

		$variables = \Variables::get_all_vars_in_expression($tokens['body']);

		// пропускаем все, которые входят в ф-ию как параметры
		$variables = array_diff($variables, $_args);

		// пропускаем предопределенные
		$variables = array_diff($variables, $this->predefined_vars);

		// пропускаем переменные, которые получают значение от функций, будучи переданными как аргумент. пример: preg_match($regexp, $mime, $matches)
		$callback_into_variable = $this->variables_assets_by_procedure($tokens['body']);
		$variables = array_diff($variables, $callback_into_variable);

		// пропускаем все, которые стоят слева от знака равенства (но только если не $a = $a; )
		$variables = $this->variables_assets_by_eq($variables, $tokens['body']);


		// пропускаем переданные по ссылке
		$var_by_ref = array();
		foreach ($this->var_pos_cache as $var_name => $var_pos) {
			if (isset($tokens['body'][$var_pos-1])
				&& $tokens['body'][$var_pos-1] === '&'
			) {
				$var_by_ref[] = $var_name;
			}
		}
		$variables = array_diff($variables, $var_by_ref);

		// игнорируем переменные, сразу инициализированные как массивы:
		$var_by_array = array();
		foreach ($this->var_pos_cache as $var_name => $var_pos) {
			if (isset($tokens['body'][$var_pos+1])
				&& $tokens['body'][$var_pos+1] === '['
			) {
				$var_by_array[] = $var_name;
			}
		}
		$variables = array_diff($variables, $var_by_array);


		// пропускаем все, которые определяются декларацией цикла
		$variables = $this->variables_assets_by_foreach($variables, $tokens['body']);

		// пропускаем все, которые глобальные или static:
		$variables = array_diff($variables, $this->variables_global($tokens['body'], 'T_GLOBAL'));
		$variables = array_diff($variables, $this->variables_global($tokens['body'], 'T_STATIC'));


		// пропускаем все, которые внутри unset (лишние ложные срабатывания)
		$unset_vars = $this->variables_as_args_of_instruction($variables, $tokens['body'], 'T_UNSET');
		$variables = array_diff($variables, $unset_vars);

		// пропускаем все, которые внутри catch()
		$catch_vars = $this->variables_as_args_of_instruction($variables, $tokens['body'], 'T_CATCH');
		$variables = array_diff($variables, $catch_vars);

		// игнорируем внутри list()
		$list_vars = $this->variables_as_args_of_instruction($variables, $tokens['body'], 'T_LIST');
		$variables = array_diff($variables, $list_vars);

		// игнорируем внутри isset
		$isset_vars = $this->variables_as_args_of_instruction($variables, $tokens['body'], 'T_ISSET');
		$variables = array_diff($variables, $isset_vars);

		// игнорируем внутри декларации анонимнйо ф-ии (array_map(function($a, $b))
		$lambda_vars = $this->variables_as_args_of_instruction($variables, $tokens['body'], 'T_FUNCTION');
		$variables = array_diff($variables, $lambda_vars);

		if (!empty($variables)) {
			//print_r($variables);
			//die();
			foreach ($variables as $var_name) {
				$this->line[] = $this->var_line[$var_name];
			}
		}
	}

	/**
	 * проверка на невозможность дальнейшего анализа
	 * @param array
	 * @return bool
	 */
	private function is_undef_behavior(array $tokens)
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
		foreach ($this->function_callback_into_variable as $func_name => $arr_func_arg_pos) {
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
		return array_unique($callback_into_variable);
	}

	/**
	 * которые стоят слева от знака равенства (но только если не $a = $a; )
	 * @param array
	 * @param array
	 * @return array
	 */
	private function variables_assets_by_eq(array $variables, array $_tokens)
	{
		$tokens_cnt = count($_tokens);
		foreach ($variables as $i => $var_name) {
			$var_pos = \Tokenizer::token_find($_tokens, \Tokenizer::get_tokens_of_expression($var_name));
			$this->var_pos_cache[$var_name] = $var_pos;
			$this->var_line[$var_name] = $_tokens[$var_pos][2];

			if ($var_pos < $tokens_cnt-1
				&& is_array($_tokens[$var_pos+2])
				&& $_tokens[$var_pos+2][0] === 'T_VARIABLE'
				&& $_tokens[$var_pos+2][1] === $var_name
			) {
				continue;
			}

			if ($var_pos < $tokens_cnt && $_tokens[$var_pos+1] === '=') {
				// если следующий символ - равно
				unset($variables[$i]);
			}
		}

		return $variables;
	}

	/**
	 * определяются декларацией цикла
	 * @param array $variables
	 * @param array $_tokens
	 * @return array
	 */
	private function variables_assets_by_foreach(array $variables, array $_tokens)
	{
		foreach ($variables as $i => $var_name) {
			$var_pos = $this->var_pos_cache[$var_name];

			if ($var_pos > 1
				&& is_array($_tokens[$var_pos-1])
				&& $_tokens[$var_pos-1][0] === 'T_AS'
			) {
				unset($variables[$i]);
			}
			elseif ($var_pos > 1
				&& is_array($_tokens[$var_pos-1])
				&& $_tokens[$var_pos-1][0] === 'T_DOUBLE_ARROW'
			) {
				// проверяем есть ли дальше переменная ,as и foreach:
				$is_var_exist = false;
				$is_as_exist = false;
				$is_foreach_exist = false;

				for ($j = $var_pos; $j>=0; $j--) {
					if ($_tokens[$j] === ';'
						|| $_tokens[$j] === '}'
						|| $_tokens[$j] === '{'
					) {
						break;
					}

					if (is_array($_tokens[$j])
						&& $_tokens[$j][0] === 'T_VARIABLE'
					){
						$is_var_exist = true;
					}

					if (is_array($_tokens[$j])
						&& $_tokens[$j][0] === 'T_AS'
					){
						$is_as_exist = true;
					}

					if (is_array($_tokens[$j])
						&& $_tokens[$j][0] === 'T_FOREACH'
					){
						$is_foreach_exist = true;
					}
				}

				if ($is_foreach_exist && $is_as_exist && $is_var_exist) {
					unset($variables[$i]);
				}
			}
		}

		return $variables;
	}

	/**
	 * пропускаем все, которые глобальные или static
	 * @param array
	 * @param string	'T_GLOBAL' | 'T_STATIC'
	 * @return array
	 */
	private function variables_global(array $_tokens, $type)
	{
		// global
		$global_vars = array();
		while (true) {
			$global_def_pos = \Tokenizer::token_ispos($_tokens, false, $type);
			if ($global_def_pos === false) {
				break;
			}

			$_tokens = array_slice($_tokens, $global_def_pos+1);

			foreach ($_tokens as $token) {
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
		return array_unique($global_vars);
	}

	/**
	 * переменные, которые являются всеми агрументами данной инструкции языка (isset, catch, list)
	 * @param array $variables
	 * @param array $_tokens
	 * @param string $type
	 * @return array
	 */
	private function variables_as_args_of_instruction(array $variables, array $_tokens, $type)
	{
		$_vars = array();
		while (true) {
			$_pos = \Tokenizer::token_ispos($_tokens, false, $type);

			if ($_pos === false) {
				break;
			}

			$_tokens = array_slice($_tokens, $_pos+1);

			foreach ($_tokens as $i => $token) {
				if (is_array($token)
					&& $token[0] === 'T_VARIABLE'
					&& (
						($_tokens[$i+1] === ')' || $_tokens[$i+1] === ',' || $_tokens[$i+1] === ';' || $_tokens[$i+1] === '}') // проверяем чтобы дальше не было -> или ::
						||
						!isset($_tokens[$i+1])
					)
				) {
					$_vars[] = $token[1];
				}

				if ($token === ')' || $token === ';' || $token === '{') {
					break;
				}
			}
		}

		return array_unique($_vars);
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