<?php

/**
 * бессмысленная и беспощадная копипаста if-ов, индусский код
 */

namespace Checkers;

class IfBlockCopyPaste extends \Analisator\ParentChecker
{
	protected $types = array(
		CHECKER_HEURISTIC
	);

	const
		MAX_REPEAT_VALUES 	= 3,
		MAX_DIFFERENCE 		= 3;	// максимальное количество позиций, в которых отличается код

	protected $error_message = 'Возмжно, индусский код (повторение If ...)';

	protected $extractor = 'Procedure';

	protected $is_line_return = true;
	protected $line = array();

	public function check($code, $full_tokens)
	{
		$this->processing($code);

		if ($code[0] === '{') {
			array_shift($code);
		}

		// учитываем блоки внутри циклов и catch
		$extractor = new \Extractors\Blocks($code);
		$code = $extractor->extract(array(
			'block' => array(
				'T_FOR',
				'T_WHILE',
				'T_FOREACH',
				'T_CATCH',
				'T_FUNCTION',
				'T_IF'
			)
		));

		//print_r($code);

		foreach ($code as $tokens) {
			$this->processing($tokens['body']);
		}

		return count($this->line) === 0;
	}

	protected function processing($code)
	{
		if ($code[0] === '{') {
			array_shift($code);
		}

		$lines = $this->to_lines($code);
		$if_clusters = $this->group_if($lines);

		// отфильтруем группы if-ов
		foreach ($if_clusters as $i => $cluster) {
			if (count($cluster) > self::MAX_REPEAT_VALUES) {
				if ($this->is_copy_paste($cluster)) {
					$this->line[] = $cluster[0][0][2];
				}
			}
		}
	}

	/**
	 * группирует токены кода по строкам, учитывая и исходное оформление кода и группировку внутри блочного оператора
	 * @param array $code
	 * @return array
	 */
	private function to_lines(array $code)
	{
		$lines = array();
		$last_line = 1;
		$block_balance = 0;
		foreach ($code as $token)
		{
			if ($token === '{') {
				$block_balance++;
			}

			if (!is_array($token) || $block_balance > 0) {
				$line = $last_line;
			}
			else {
				$line = $token[2];
			}

			if (!isset($lines[$line])) {
				$lines[$line] = array();
			}

			$lines[$line][] = $token;

			$last_line = $line;

			if ($token === '}') {
				$block_balance--;
			}
		}

		// для отладки
		/*foreach ($lines as $i => $line) {
			$lines[$i] = \Tokenizer::tokens_to_source($line);
		}

		print_r(array_values($lines));
		die(PHP_EOL);*/

		return array_values($lines);
	}

	private static function is_if($token)
	{
		return is_array($token) && $token[0] === 'T_IF';
	}

	/**
	 * сгруппировать подряд идующие if-ы
	 * @param array $lines
	 * @return array
	 */
	private function group_if(array $lines)
	{
		$clusters = array();
		$cluster = 0;
		$prev = -1;
		foreach ($lines as $i => $line) {
			if (self::is_if($line[0]) && ($i - $prev === 1)) {
				if (!isset($clusters[$cluster])) {
					$clusters[$cluster] = array();
				}

				$clusters[$cluster][] = $line;
			}
			else {
				$cluster++;
			}

			$prev = $i;
		}

		/*foreach ($clusters as $i => $cluster) {
			foreach ($cluster as $j => $line) {
				$clusters[$i][$j] = \Tokenizer::tokens_to_source($line);
			}
		}
		print_r($clusters);
		*/

		return $clusters;
	}

	private function is_copy_paste(array $lines) {

		// собираем отличия от первого выражения
		$first = array_shift($lines);
		$diffs = array();
		foreach ($lines as $line) {
			$diff = \Utils::tokens_diff($first, $line);
			$diffs[] = array(array_filter($diff[0]), array_filter($diff[1]));
		}

		// кол-во различий должно быть одинаково
		$cnt = -1;
		foreach ($diffs as $diff) {
			if ($cnt < 0) {
				$cnt = count($diff[0]);
			}
			elseif ($cnt !== count($diff[0]) || $cnt > self::MAX_DIFFERENCE) {
				return false;
			}
		}

		// отличия должны быть на одинаковых местах
		$places = array();
		foreach ($diffs as $diff) {
			$places[] = array_keys($diff[0]);
		}
		$places = array_unique($places);
		if (count($places) > $cnt) {
			return false;
		}

		// отличия должны касаться только констант
		$check_on_const = function($value)
		{
			if (is_array($value)) {
				return in_array($value, array(
					'T_LNUMBER',
					'T_STRING',
					'T_CONSTANT_ENCAPSED_STRING'
				));
			}
			else {
				return true;
			}
		};

		foreach ($diffs as $diff) {
			if (count(array_filter($diff[0], $check_on_const)) > 0) {
				return false;
			}

			if (count(array_filter($diff[1], $check_on_const)) > 0) {
				return false;
			}
		}

		return true;
	}
}