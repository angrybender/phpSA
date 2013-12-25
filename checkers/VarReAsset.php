<?php
/**
 * переобозначение переменной
 * 	$var = 1234;
	..
	..
	$var = 456; // зачем дважды?
 *
 * @author k.vagin
 */

namespace Checkers;


class VarReAsset extends VarUndefined
{
	protected $types = array(
		CHECKER_HEURISTIC
	);

	protected $error_message = 'Переменная переобозначается ниже, а до этого не используется';

	protected $variables_index = array();
	protected $variables_reverse_index = array();
	private $suspicious_var = array();

	private $block_head = array(
		'T_IF',
		'T_FOR',
		'T_FOREACH',
		'T_SWITCH',
		'T_CASE',
		'T_WHILE',
		'T_TRY',
		'T_CATCH'
	);

	/**
	 * анализирует отдельный блок кода
	 * ошибки добавляет сама
	 *
	 * @param $tokens
	 * @return bool
	 */
	protected function analize_code($tokens)
	{
		$_args = \Variables::get_all_vars_in_expression($tokens['declaration']);

		$variables = \Variables::get_all_vars_in_expression($tokens['body']);
		$variables = array_unique(array_merge($_args, $variables));

		// пропускаем предопределенные
		$variables = array_diff($variables, $this->predefined_vars);

		// индекс переменных:
		$this->build_variables_index($variables, $tokens['body']);

		// пропускаем все, которые встречаются 1 раз
		foreach ($variables as $i => $var_name) {
			if (count($this->variables_index[$var_name]) < 2) {
				unset($variables[$i]);
			}
		}

		// пропускаем внутри ветвлений if, циклов и тд
		$variables = $this->variable_inside_block($variables, $tokens['body']);

		foreach ($variables as $i => $var_name) {
			$this->process_var($var_name, $tokens['body']);
		}

		// пост фильтрация:
		$this->post_checked($tokens['body']);

		foreach ($this->suspicious_var as $var_name) {
			$this->line[] = $this->variables_index[$var_name][0];
		}
	}

	/**
	 * переменные вунтри блочных операторов и if
	 * @param array $variables
	 * @param array $tokens
	 * @return array
	 */
	private function variable_inside_block(array $variables, array $tokens)
	{
		foreach ($variables as $i => $var_name) {

			foreach ($this->variables_index[$var_name] as $var_pos)
			{
				for ($j = $var_pos-1; $j>0; $j--) {
					if ($tokens[$j] === '{'
						|| is_array($tokens[$j]) && in_array($tokens[$j][0], $this->block_head)
					) {
						unset($variables[$i]);
						break;
					}
				}
			}

		}

		return $variables;
	}

	/**
	 * строит индекс - имя_переменной - массив позиций
	 * @param array $variables
	 * @param array $tokens
	 */
	protected function build_variables_index(array $variables, array $tokens)
	{
		foreach ($variables as $var_name) {
			$_tokens = $tokens;
			$this->variables_index[$var_name] = array();
			$prev_pos = 0;
			while (true) {
				$var_pos = \Tokenizer::token_ispos($_tokens, $var_name, 'T_VARIABLE');
				if ($var_pos === false) {
					break;
				}

				$_tokens = array_slice($_tokens, $var_pos + 1);

				$var_pos = $var_pos + $prev_pos;

				$this->variables_index[$var_name][] = $var_pos;
				$this->variables_reverse_index[$var_pos] = $var_name;

				$prev_pos = $var_pos + 1;
			}
		}
	}

	/**
	 * проверить переменную
	 * @param string $var_name
	 * @param array $tokens
	 */
	protected function process_var($var_name, array $tokens)
	{
		foreach ($this->variables_index[$var_name] as $i => $var_position) {
			if (!isset($this->variables_index[$var_name][$i+1])) break;

			$curr_pos = $var_position;
			$next_pos = $this->variables_index[$var_name][$i+1];

			// если в рядом стоящих позициях переменной что то присваивается - алерт:
			if (!isset($tokens[$next_pos+1])) {
				// хрень какая то но надо
				break;
			}

			if ($tokens[$curr_pos+1] === '='
				&& $tokens[$next_pos+1] === '='
			) {
				$this->suspicious_var[] = $var_name;
			}
		}

	}

	/**
	 * допроверка
	 * @param array $tokens
	 * @return int
	 */
	private function post_checked(array $tokens)
	{
		if (empty($this->suspicious_var)) {
			return 0;
		}

		$lines = \Tokenizer::format_code_into_lines($tokens);
		$lines = array_filter($lines, function($val) // оптимизируем, выбрасывая строки, которые заведомо не будем обрабатывать
		{
			return (strpos($val, '=') !== false || strpos($val, '=') !== false);
		});

		foreach ($this->suspicious_var as $i => $var_name) {
			foreach ($lines as $line) {
				if (stripos($line, $var_name) === false) continue;

				if ($line === "{$var_name}={$var_name}") break;

				$line_tokens = \Tokenizer::get_tokens_of_expression($line);
				$var_cnt = 0;
				foreach ($line_tokens as $token) {
					if (is_array($token) && $token[0] === 'T_VARIABLE' && $token[1] === $var_name) {
						$var_cnt++;
					}

					if ($var_cnt > 1) break;
				}

				if ($var_cnt > 1) {
					unset($this->suspicious_var[$i]);
					break;
				}
			}
		}
	}
} 