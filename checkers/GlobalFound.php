<?php
/**
 * global - плохой стиль
 * @author k.vagin
 */

namespace Checkers;


class GlobalFound
{

	protected $types = array(
		CHECKER_HEURISTIC
	);

	protected $error_message = 'Global - плохой стиль';

	protected $extractor = 'Full'; // класс-извлекатель нужных блоков

	protected $is_line_return = true; // по умолчанию, строка ошибки определяется по началу блока, но функция проверки  может ее переопределить
	protected $line = array();

	/**
	 * @param array $tokens
	 * @return bool
	 */
	public function check($tokens, $full_tokens)
	{
		$calle = array();
		foreach ($tokens as $i => $token) {
			if (is_array($token)
				&& $token[0] === 'T_GLOBAL'
			) {

				$this->line[] = $token[2];
			}
		}

		return empty($this->line);
	}
} 