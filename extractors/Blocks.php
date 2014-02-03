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
		'T_FOREACH' => 'T_ENDFOREACH',
		'T_FUNCTION' => NULL,
		'T_CATCH' => NULL
	);

	/**
	 * извлекает из кода любые блочные конструкции - циклы, условные ветвления и тд
	 * вида BLOCK ( %some expr% ) ...
	 *
	 * вместе с вложенными
	 * @param array $filter array('block' => block_token_type) | $filter array('block' => array(block_token_type1, block_token_type2))
	 * @return array|void
	 */
	public function extract(array $filter)
	{
		$blocks = array();

		if (is_scalar($filter['block'])) {
			$filter['block'] = array($filter['block']);
		}

		$block = 0;
		$brackets = 0;
		$items = array();
		$is_start = false;
		$end_token = '';
		$start_line = 0;
		$this_filter_token = '';
		foreach ($this->tokens as $i => $token) {
			if (is_array($token)
				&& in_array($token[0], $filter['block'])
			) {
				$block++;
				$this_filter_token = $token[0];
				if ($block === 1) {
					$start_line = $token[2];
					continue;
				}
				elseif ($block > 1 && $this->is_block_inline(array_slice($this->tokens, $i+1), $this_filter_token)) {
					// костыль для однострочных вложенных того же вида
					// до исправления неверно отрабатывал на /tests/data/checker_if_block_copy_paste/bad/2.php
					$block--;
				}
			}

			if ($block>0 && $token === '(') {
				$brackets++;
			}

			if ($block>0 && $token === ')') {
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
				($token === $end_token || is_array($token) && ($token[0] === $this->ended_tokens[$this_filter_token]))
			) {
				$block--;
			}
			//echo 'BLOCK: ' . $block . PHP_EOL;

			if (!empty($items) && $block === 0) {
				if ($items[0] === ':') {
					unset($items[0]);
				}
				$is_start = false;
				$blocks[] = array(
					'body' => $items,
					'type' => $this_filter_token,
					'line' => $start_line
				);

				$items = array();
			}
		}

		return $blocks;
	}

	/**
	 * @param array $tokens
	 * @param string $filter
	 * @return bool
	 */
	protected function is_block_inline(array $tokens, $filter)
	{
		$block = 0;
		$brackets = 0;
		$items = array();
		$is_start = false;
		foreach ($tokens as $i => $token) {
			if (is_array($token)
				&& $token[0] === $filter
			) {
				$block++;
				if ($block === 1) {
					continue;
				}
			}

			if ($block>0 && $token === '(') {
				$brackets++;
			}

			if ($block>0 && $token === ')') {
				$brackets--;
			}

			if ($is_start) {
				$items[] = $token;
			}

			if ($block>0 && $brackets == 0 && !$is_start) { // кончилось выражение (...) в начале
				$is_start = true;

				if (isset($tokens[$i+1]) && $tokens[$i+1] === '{') {
					return false;
				}
				else {
					return true;
				}

				continue;
			}
		}

		return true;
	}
}