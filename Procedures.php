<?php
/**
 * работа с функциями, методами
 * @author k.vagin
 */

class Procedures {

	/**
	 * выводит список всех процедур / функций, вызываемых внутри переданного кода
	 * @param string|array $code
	 * @return array
	 */
	public static function get_all_procedures_in_code($code)
	{
		if (!is_array($code)) {
			if (!Tokenizer::is_open_tag($code)) {
				$code = '<?php ' . $code;
			}

			$tokens = Tokenizer::get_tokens($code, true);
		}
		else {
			$tokens = $code;
		}

		$code = null;
		$results = array();

		foreach ($tokens as $i => $token) {
			if (!is_array($token)) {
				continue;
			}

			if ($token[0] == 'T_STRING'
				&& isset($tokens[$i+1])
				&& $tokens[$i+1] === '('
			) {
				//либо токен в самом начале файла, либо перед ним нет никаких операторов работы с классами/объектами
				$is_add = true;
				if (isset($tokens[$i-1])
					&& is_array($tokens[$i-1])
					&& ($tokens[$i-1][0] == 'T_OBJECT_OPERATOR' || $tokens[$i-1][0] == 'T_DOUBLE_COLON' || $tokens[$i-1][0] == 'T_NEW')
				) {
					$is_add = false;
				}

				if ($is_add) {
					$results[] = $token[1];
				}
			}
		}

		return $results;
	}

	/**
	 * раскручивает цепочку и возвращает всю конструктцию вызова метода целиком $this->getObj()::instance()
	 * @param array $tokens
	 * @param int $token_position
	 * @return string
	 */
	public static function extract_full_class_method(array $tokens, $token_position)
	{

	}
} 