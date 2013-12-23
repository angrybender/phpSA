<?php
/**
 *
 * @author k.vagin
 */

namespace Extractors;


class Conditions extends \Analisator\ParentExtractor {

	/**
	 * извлекает из кода информацию о условиях if, elseif, while
	 * @return array
	 */
	public function extract()
	{
		$conditions = array();

		$ar_need_tokens = array(
			'T_IF',
			'T_ELSEIF',
			'T_WHILE'
		);

		$tokens = $this->tokens;
		foreach ($tokens as $i => $token) {
			if (is_array($token) && in_array($token[0], $ar_need_tokens)) {
				$conditions[] = array(
					'body' => \Tokenizer::find_full_first_expression(array_slice($tokens, $i+1), '(', ')'),
					'type' => $token[0],
					'line' => $token[2]
				);
			}
		}

		return $conditions;
	}
} 