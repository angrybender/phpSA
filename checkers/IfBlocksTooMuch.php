<?php
/**
 * очень много вложенных if-ов путает код
 * todo переработать учет вложенности добавить учет расстояния
 * @author k.vagin
 */

namespace Checkers;


class IfBlocksTooMuch extends \Analisator\ParentChecker
{
	protected $types = array(
		CHECKER_HEURISTIC
	);

	protected $error_message = 'Слишком большое количество вложенных If';

	protected $extractor = 'Blocks'; // класс-извлекатель нужных блоков
	protected $filter = array(
		'block' => 'T_IF'
	);

	private $max = 4;

	public function check($code, $full_tokens)
	{
		$tokens = \Tokenizer::get_tokens_of_expression($code);
		$cnt = 0;
		$last_ifpos = 0;
		while (true) {
			$if_pos = \Tokenizer::token_ispos($tokens, false, 'T_IF');
			if ($if_pos !== false) {
				$tokens = array_slice($tokens, $if_pos+1);

				$cnt++;

				if ($cnt > 1 && ($if_pos - $last_ifpos > 10)) { // если они далеко друг от друга - хрен с ним, а то уж слишком много срабатываний
					$cnt--;
				}
			}
			else {
				break;
			}

			$last_ifpos = $if_pos;
		}

		return ($cnt < $this->max) || ($this->recursive_if_counter($code, 0) < $this->max);
		// 2 классификатора для минимизации ложных срабатываний
		// один учитывает вложенность, другой - расстояние
	}

	private function recursive_if_counter($code, $last_count)
	{
		$if_extractor = new \Extractors\Blocks($code);
		$ifs = $if_extractor->extract($this->filter);

		$count = 0;
		foreach ($ifs as $if) {
			$count = $this->recursive_if_counter($if['body'], $last_count+1);
			if ($count > $this->max) {
				return ($count + $last_count);
			}
		}

		return $count + $last_count;
	}
} 