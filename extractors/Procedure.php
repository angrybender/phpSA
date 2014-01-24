<?php
/**
 *
 * @author k.vagin
 */

namespace Extractors;


class Procedure extends \Analisator\ParentExtractor {

	/**
	 * извлекает из кода информацию о процедурах, функциях, методах (замыкания и анонимки пропускает)
	 * @param array
	 * @return array
	 */
	public function extract(array $filter = null)
	{
		$tokens = $this->tokens;
		$function = array();
		$is_func_found = false;
		$brackets_balance = 0;
		$name = '';
		$line = 0;
		$start = 0;
		foreach ($tokens as $i => $token) {

			if ($i === 0) continue;

			if (
				!$is_func_found
				&& is_array($token)
				&& $token[0] === 'T_STRING'
				&& isset($tokens[$i-1])
				&& is_array($tokens[$i-1])
				&& $tokens[$i-1][0] === 'T_FUNCTION'
			) {
				$is_func_found = true;
				$name = $token[1];
				$line = $token[2];
				$start = $i+1;
				continue;
			}

			if ($is_func_found && $token === '(') {
				$brackets_balance++;
			}

			if ($is_func_found && $token === ')') {
				$brackets_balance--;
			}

			if ($is_func_found && $brackets_balance === 0) {
				$body = array();
				if (isset($tokens[$i+1]) && $tokens[$i+1] === '{') { // есть еще абстрактные методы и интерфейсы
					$body = \Tokenizer::find_full_first_expression(array_slice($tokens, $i+1), '{', '}', true);
				}

				$function[] = array(
					'name' => $name,
					'body' => $body,
					'declaration' => array_slice($tokens, $start+1, $i-$start-1),
					'line' => $line
				);

				$is_func_found = false;
			}
		}

		return $function;
	}

} 