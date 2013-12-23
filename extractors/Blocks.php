<?php
/**
 *
 * @author k.vagin
 */

namespace Extractors;


class Blocks extends \Analisator\ParentExtractor {

	private $ended_tokens = array(
		'T_IF' => 'T_ENDIF',
		'T_FOR' => 'T_ENDFOR',
		'T_WHILE' => 'T_ENDWHILE',
		'T_FOREACH' => 'T_ENDFOREACH'
	);

	/**
	 * извлекает из кода любые блочные конструкции - циклы, условные ветвления и тд
	 * вида BLOCK ( %some expr% ) ...
	 *
	 * вместе с вложенными
	 * @param array $filter array('block' => block_token_type)
	 * @return array|void
	 */
	public function extract(array $filter)
	{
		$blocks = array();

		$block = 0;
		$brackets = 0;
		$items = array();
		$is_start = false;
		$end_token = '';
		$start_line = 0;
		foreach ($this->tokens as $i => $token) {
			if (is_array($token)
				&& $token[0] === $filter['block']
			) {
				$block++;
				if ($block === 1) {
					$start_line = $token[2];
					continue;
				}
			}

			if ($blocks>0 && $token === '(') {
				$brackets++;
			}

			if ($blocks>0 && $token === ')') {
				$brackets--;
			}

			if ($is_start) {
				$items[] = $token;
			}

			if ($block>0 && $brackets == 0 && !$is_start) { // кончилось выражение (...) в начале
				$is_start = true;

				if (isset($this->tokens[$i+1]) && $this->tokens[$i+1] === '{') {
					$end_token = '}';
				}
				else {
					$end_token = ';';
				}

				continue;
			}

			if ($is_start &&
				($token === $end_token || is_array($token) && ($token[0] === $this->ended_tokens[$filter['block']]))
			) {
				$block--;
			}

			if (!empty($items) && $block === 0) {
				if ($items[0] === ':') {
					unset($items[0]);
				}
				$is_start = false;
				$blocks[] = array(
					'body' => \Tokenizer::tokens_to_source($items),
					'type' => $filter['block'],
					'line' => $start_line
				);

				$items = array();
			}
		}

		return $blocks;
	}

} 