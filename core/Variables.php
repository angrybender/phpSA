<?php
/**
 *
 * @author k.vagin
 */

class Variables {
	/**
	 * выводит массив имен переменных внутри выражения без повторов
	 * @param string $expression
	 * @return array
	 */
	public static function get_all_vars_in_expression($expression)
	{
		$result = array();
		if (!Tokenizer::is_open_tag($expression)) {
			$expression = '<?php ' . $expression;
		}

		$tokens = Tokenizer::get_tokens($expression);
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
} 