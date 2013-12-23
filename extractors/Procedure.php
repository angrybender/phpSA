<?php
/**
 *
 * @author k.vagin
 */

namespace Extractors;


class Procedure extends \Analisator\ParentExtractor {

	/**
	 * извлекает из кода информацию о процедурах, функциях, методах (замыкания и анонимки пропускает)
	 * @return array
	 */
	public function extract(array $filter = null)
	{
		$tokens = $this->tokens;
		$function = array();
		foreach ($tokens as $i => $token) {
			if (is_array($token)
				&& $token[0] === 'T_FUNCTION'
				&& isset($tokens[$i+1])
				&& is_array($tokens[$i+1])
				&& $tokens[$i+1][0] == 'T_STRING'
			) {

				$open_block_position = \Tokenizer::token_ispos(array_slice($tokens, $i), '{');

				if ($open_block_position !== false) {
					$function[] = array(
						'name' => $tokens[$i+1][1],
						'body' => \Tokenizer::find_full_first_expression(array_slice($tokens, $i+$open_block_position), '{', '}'),
						'line' => $token[2]
					);
				}
			}
		}

		return $function;
	}

} 