<?php
/**
 * подряд несколько вызовов str_replace если операция производится над одной переменной
 * пропускает если подряд идут 2 вызова (слишком часто такой код встречается)
 *
 * ищет самые тупые варианты. не рассматривает, когда результат подается на вход следующей ф-ии т.к. ситуация :
 * 	$e = str_replace("s", "", $d);
	$e = str_replace("g", "", $e);
 * это todo
 * @author k.vagin
 */

namespace Checkers;


class StrReplaceOptimal extends \Analisator\ParentChecker
{

	protected $types = array(
		CHECKER_HEURISTIC
	);

	protected $error_message = 'str_replace умеет принимать массив аргументов, можно избежать дублирования';

	protected $extractor = 'Full'; // класс-извлекатель нужных блоков

	protected $is_line_return = true; // по умолчанию, строка ошибки определяется по началу блока, но функция проверки  может ее переопределить
	protected $line = array();

	/**
	 * @param array $code
	 * @return bool
	 */
	public function check($tokens)
	{
		$calle = array();
		foreach ($tokens as $i => $token) {
			if (is_array($token)
				&& $token[0] === 'T_STRING'
				&& isset($tokens[$i+1])
				&& $tokens[$i+1][0] == '('
				&& strtolower($token[1]) === 'str_replace'
				&& ($i>0)
				&& $tokens[$i-1] === '='
			) {

				$_calle = \Tokenizer::find_full_first_expression(array_slice($tokens, $i+1), '(', ')');

				$calle[] = array(
					'name' => $token[1],
					'body' => $_calle,
					'line' => $token[2]
				);
			}
		}

		// пропускаем везде, где массивы в качестве аргументов
		foreach ($calle as $i => $call) {
			if (\Tokenizer::token_ispos($call['body'], false, 'T_ARRAY')) {
				unset($calle[$i]);
			}
		}
		$calle = array_values($calle);

		// отбираем те, что стоят рядом
		$clusters = array();
		$cluster = 0;
		$prev = 0;
		foreach ($calle as $i => $call) {
			if (!isset($clusters[$cluster])) {
				$clusters[$cluster] = array();
			}

			if ($prev > 0 && ($call['line'] - $prev > 1)) {
				$cluster++;
			}

			$clusters[$cluster][] = $call;

			$prev = $call['line'];
		}

		// проверяем на переменные
		foreach ($clusters as $group) {
			if (count($group) < 3) {
				continue;
			}

			$str_var = '';
			$is_eq = true;
			foreach ($group as $i => $item) {
				$curr_var = $this->extract_string_of_str_replace( substr($item['body'], 1) );  // обрезаем первую скобку. допустимо т.к. структура детерминирована
				if ($i === 0) {
					$str_var = $curr_var;
				}

				$is_eq = $is_eq && ($str_var === $curr_var);

				$str_var = $curr_var;

				if (!$is_eq) { // легкая оптимизация...
					break;
				}
			}

			if ($is_eq) {
				$this->line[] = $group[0]['line'];
			}
		}



		return empty($this->line);
	}

	/**
	 * извлекает третий аргумент ф-ии str_replace
	 * @param string
	 * @return array
	 */
	private function extract_string_of_str_replace($_args = "")
	{
		$tokens = \Tokenizer::get_tokens_of_expression($_args);

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

			if ($comma_cnt === 2) {
				$result_tokens = array_slice($tokens, $i+1);
				break;
			}
		}

		return strtolower(\Tokenizer::tokens_to_source($result_tokens));
	}
} 