<?php
namespace Checkers;

class ViewAndLogicSpaghetti
{
	protected $types = array(
		CHECKER_HEURISTIC
	);

	protected $error_message = 'Возможно смешение логики и представления';

	protected $extractor = 'Procedure'; // класс-извлекатель нужных блоков

	public function check($code, $full_tokens)
	{
		$concat_operations = 0;
		$tag_string_count = 0;
		$lines = array();
		$all_lines = array();
		$avg_length = 0;

		$excluded = $this->excluded_strings($code);

		foreach ($code as $i => $token) {
			if (is_array($token)) {
				$all_lines[$token[2]] = 1;
			}

			if ($token === '.') {
				$concat_operations++;
			}
			elseif (
				is_array($token)
				&& $token[0] === 'T_CONSTANT_ENCAPSED_STRING'
				&& !isset($excluded[$token[1]])
				&& (strpos($token[1], '<') !== false || strpos($token[1], '>') !== false )
				&& strpos($token[1], '?php') === false
				&& strpos($token[1], '<=') === false // отсекаем регулярки
				&& strpos($token[1], '=>') === false
				&& (!isset($code[$i+1]) || $code[$i+1] !== ',') // аргументы функций
				&& (!isset($code[$i+1]) || $code[$i+1] !== ')')
			) {
				$avg_length = $avg_length + strlen($token[1]);
				$tag_string_count++;
				$lines[$token[2]] = 1;
			}
			elseif (
				is_array($token)
				&& $token[0] === 'T_CONSTANT_ENCAPSED_STRING'
				&& strpos($token[1], '?php') !== false
			) {
				$tag_string_count--;
				unset($lines[$token[2]]);
			}
		}

		$avg_length = ($tag_string_count == 0) ? 0 : round($avg_length/$tag_string_count);
		$tokens_count = count(array_keys($all_lines));

		//echo PHP_EOL, $concat_operations/$tokens_count, PHP_EOL;
		//echo $tag_string_count/$tokens_count, PHP_EOL;
		//echo 'tokens_count: ', $tokens_count, PHP_EOL;
		//echo 'avg_length : ', $avg_length, PHP_EOL;
		//print_r($lines);

		// как всегда магия
		return !($concat_operations/$tokens_count>=0.8 && $tag_string_count/$tokens_count>=0.2 && (count(array_keys($lines))>1 && $tag_string_count>3) || $avg_length>=20);
	}

	/**
	 * строки, которые участвуют как аргументы методов, которые говорят о том, что тут вряд ли формирование хтмл тела
	 * @param $code
	 * @return array
	 */
	private function excluded_strings($code)
	{
		$called_functions = new \Extractors\CalledFunction($code);
		$ar_called_functions = $called_functions->extract(array(
			'name' => array(
				'preg_match',
				'preg_replace'
			)
		));

		$excluded = array();
		foreach ($ar_called_functions as $info) {
			$body_tokens = \Tokenizer::get_tokens_of_expression($info['body']);
			foreach ($body_tokens as $token) {
				if (is_array($token)
					&& $token[0] === 'T_CONSTANT_ENCAPSED_STRING'
				) {
					$excluded[$token[1]] = 1;
				}
			}
		}

		return $excluded;
	}
}