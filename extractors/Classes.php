<?php
/**
 *
 * @author k.vagin
 */

namespace Extractors;


class Classes extends \Analisator\ParentExtractor {

	/**
	 * // todo имя класса-родителя
	 * извлекает из кода информацию о классах, функциях, методах (замыкания и анонимки пропускает)
	 * @param $filter
	 * @return array
	 */
	public function extract(array $filter = null)
	{
		$tokens = $this->tokens;
		$classes = array();
		foreach ($tokens as $i => $token) {
			if (is_array($token)
				&& $token[0] === 'T_CLASS'
				&& isset($tokens[$i+1])
				&& is_array($tokens[$i+1])
				&& $tokens[$i+1][0] == 'T_STRING'
			) {

				$open_block_position = \Tokenizer::token_ispos(array_slice($tokens, $i), '{');

				if ($open_block_position !== false) {
					$classes[] = array(
						'name' => $tokens[$i+1][1],
						'body' => \Tokenizer::find_full_first_expression(array_slice($tokens, $i+$open_block_position), '{', '}', true),
						'line' => $token[2]
					);
				}
			}
		}

		return $classes;
	}
} 