<?php
/**
 *
 * @author k.vagin
 */

class Heuristic {

	public static $sql_tokens = array(
		'select',
		'where',
		'from',
		'limit',
		'alter',
		'update',
		'remove',
		'insert',
		'table',
	);

	/**
	 * пытается угдать - похожа ли строка на sql запрос
	 * @param $string
	 * @return bool
	 */
	public static function is_maybe_sql_query($string)
	{
		$string = function_exists('mb_strtolower') ? mb_strtolower($string, 'UTF-8') : strtolower($string);
		$string = preg_replace('/\"(.+)\"/', '', $string);
		$string = preg_replace('/\'(.+)\'/', '', $string);
		$string = preg_replace('/\`(.+)\`/', '', $string);

		if (strpos($string, '<select') !== false) {
			return false;
		}

		$ar_words = preg_split('/[\s]/', $string);

		return count(array_intersect($ar_words, self::$sql_tokens)) >= 2;
	}

	/**
	 * определяет, похожи ли 2 куска кода
	 * @param $code1
	 * @param $code2
	 * @param bool $strong
	 * @return bool
	 */
	public static function code_similar(array $code1, array $code2, $strong = true)
	{
		if (!$strong) {
			return self::week_code_similar($code1, $code2);
		}

		$removed_tokens = array(
			'T_COMMENT',
			'T_DOC_COMMENT',
			'T_VARIABLE',
			//'T_CURLY_OPEN',
			'T_CONSTANT_ENCAPSED_STRING',
		);

		$code1 = \Tokenizer::remove_token_by_type($code1, $removed_tokens);
		$code2 = \Tokenizer::remove_token_by_type($code2, $removed_tokens);

		// оборачивание в {} однострочных управляющих конструкций
		$code1 = self::wrap_one_line_blocks($code1);
		$code2 = self::wrap_one_line_blocks($code2);

		// удаление };
		$code1 = \Tokenizer::remove_token_by_type($code1, array('}',';'));
		$code2 = \Tokenizer::remove_token_by_type($code2, array('}',';'));

		if (count($code1) !== count($code2)) {
			return false;
		}

		$logical_replace = function($tokens) {
			// or -> || , AND -> &&
			foreach ($tokens as $i => $token) {
				if (is_array($token) && ($token[0] === 'T_LOGICAL_OR')) {
					$tokens[$i] = array(
						'T_BOOLEAN_OR',
						'||'
					);
				}

				if (is_array($token) && ($token[0] === 'T_LOGICAL_AND')) {
					$tokens[$i] = array(
						'T_BOOLEAN_AND',
						'&&'
					);
				}
			}

			return $tokens;
		};

		$code1 = $logical_replace($code1);
		$code2 = $logical_replace($code2);

		// return remove:
		$return_remove = function($tokens) {
			foreach ($tokens as $i => $token) {
				if (is_array($token) && $token[0] === 'T_RETURN') {
					return array_slice($tokens, $i-1);
				}
			}
		};

		$code1 = $return_remove($code1);
		$code2 = $return_remove($code2);

		$code1 = \Tokenizer::tokens_to_source($code1);
		$code2 = \Tokenizer::tokens_to_source($code2);

		if (empty($code1) || empty($code2)) {
			return false;
		}

		return strtolower($code1) === strtolower($code2);
	}

	protected static function wrap_one_line_blocks(array $tokens)
	{
		$block_head = array(
			'T_IF',
			'T_FOR',
			'T_FOREACH',
			'T_WHILE',
		);

		$block_start = 0;
		$brackets = 0;
		$new_tokens = array();
		$is_open_block = false;
		foreach ($tokens as $i => $token) {

			$new_tokens[] = $token;

			if (is_array($token) && in_array($token[0], $block_head)) {
				$block_start++;
				$is_open_block = false;
				continue;
			}

			if ($block_start>0 && $token === '(') {
				$brackets++;
			}

			if ($block_start>0 && $token === ')') {
				$brackets--;
			}

			if (!$is_open_block
				&& $block_start>0
				&& $brackets === 0
				&& isset($tokens[$i+1])
				&& $tokens[$i+1] !== '{'
			) {
				$is_open_block = true;
				$new_tokens[] = '{';
			}

			if ($block_start>0 && $token === ';') {
				$new_tokens = array_merge($new_tokens, array_fill(0, $block_start, '}'));
				$block_start = 0;
			}
		}

		return $new_tokens;
	}

	/**
	 * определяет примерно, похожи ли 2 куска кода
	 * @param $code1
	 * @param $code2
	 * @return bool
	 */
	public static function week_code_similar(array $code1, array $code2)
	{
		$removed_tokens = array(
			'T_COMMENT',
			'T_DOC_COMMENT',
			'T_VARIABLE',
			'T_CONSTANT_ENCAPSED_STRING',
		);

		$code1 = \Tokenizer::remove_token_by_type($code1, $removed_tokens);
		$code2 = \Tokenizer::remove_token_by_type($code2, $removed_tokens);

		return false;
	}
} 