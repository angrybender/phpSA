<?php
/**
 *
 * @author k.vagin
 */

class CopyPaste {

	/**
	 * упрощает код, чтобы его можнжо было сравнивать, выбрасывает незначащие конструкции. совершает преобразования
	 * чтобы нивелировать различия
	 *
	 * @param $code1
	 * @return string
	 */
	public static function code_simplification(array $code1)
	{
		$code1 = \Tokenizer::remove_comments($code1);

		// оборачивание в {} однострочных управляющих конструкций
		$code1 = self::wrap_one_line_blocks($code1);

		$removed_tokens = array(
			'T_VARIABLE',
			//'T_CURLY_OPEN',
			'T_CONSTANT_ENCAPSED_STRING',
			'T_OPEN_TAG',
		);

		$code1 = \Tokenizer::remove_token_by_type($code1, $removed_tokens);

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

		// return remove:
		$return_remove = function($tokens) {
			foreach ($tokens as $i => $token) {
				if (is_array($token) && $token[0] === 'T_RETURN') {
					return array_slice($tokens, 0, $i);
				}
			}
		};

		$code1 = $return_remove($code1);

		$code1 = \Tokenizer::tokens_to_source($code1);

		return strtolower($code1);
	}

	protected static function wrap_one_line_blocks(array $tokens)
	{
		$block_head = array(
			'T_IF',
			'T_ELSEIF',
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

			if (is_array($token)
				&& $token[0] === 'T_ELSE'
				&& isset($tokens[$i+1])
				&& $tokens[$i+1] !== '{'
			) {
				$block_start++;
				$new_tokens[] = '{';
			}

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
				&& $token === ')'
				&& isset($tokens[$i+1])
				&& $tokens[$i+1] !== '{'
			) {
				$is_open_block = true;
				$new_tokens[] = '{';
			}
			elseif ($brackets === 0
				&& $token === ')'
				&& $block_start>0
			) {
				$block_start--;
			}

			if ($block_start>0 && $token === ';') {
				$new_tokens = array_merge($new_tokens, array_fill(0, $block_start, '}'));
				$block_start = 0;
			}
		}

		return $new_tokens;
	}
} 