<?php
/**
 *
 * @author k.vagin
 */

class Utils {

	/**
	 * получить массив всех возможных булевских значений для N переменных (их ,как известно 2^N)
	 * @param int $count
	 * @throws Exception
	 * @return array
	 */
	public static function get_all_variables($count=0)
	{
		if ($count>=10) {
			throw new \Exception("слишком большое число переменных");
		}

		$variants = pow(2, $count);
		$result = array();
		$register = array_fill(0, $count, 0);

		for ($i=0; $i<$variants; $i++) {
			$result[] = $register;

			$register[$count-1] = 1 - $register[$count-1];
			for ($j = $count-1; $j>0; $j--) {
				if ($register[$j]==0) { // перенос разряда
					$register[$j-1] = 1 - $register[$j-1];
				}
				else {
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * Git-style
	 * Принимает вектора хешей;
	 * На одинаковых местах будет NULL
	 * @param array $a
	 * @param array $b
	 * @return array
	 */
	public static function tokens_diff(array $a, array $b)
	{
		$a = array_values($a);
		$b = array_values($b);

		$cnt_a = count($a);
		$cnt_b = count($b);
		$cnt = $cnt_a;
		if ($cnt_a > $cnt_b) {
			$cnt = $cnt_b;
		}

		for ($i = 0; $i < $cnt; $i++) {
			if (\Tokenizer::tokens_is_eq($a[$i], $b[$i], true)) {
				$a[$i] = NULL;
				$b[$i] = NULL;
			}
		}

		return array(
			$a, $b
		);
	}

} 