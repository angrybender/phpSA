<?php
/**
 *
 * @author k.vagin
 */

namespace Extractors;


class CalledFunction extends \Analisator\ParentExtractor {

	/**
	 * извлекает из кода вызовы функций
	 */
	public function extract(array $filter)
	{
		if (is_scalar($filter['name'])) {
			$filter['name'] = array($filter['name']);
		}

		$tokens = $this->tokens;
		$function = array();
		foreach ($tokens as $i => $token) {
			if (is_array($token)
				&& $token[0] === 'T_STRING'
				&& isset($tokens[$i+1])
				&& $tokens[$i+1][0] == '('
				&& in_array(strtolower($token[1]), $filter['name'])
			) {

				$calle = \Tokenizer::find_full_first_expression(array_slice($tokens, $i+1), '(', ')');

				$function[] = array(
					'name' => $token[1],
					'body' => $calle,
					'line' => $token[2]
				);
			}
		}

		return $function;
	}

} 