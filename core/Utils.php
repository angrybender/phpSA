<?php
/**
 *
 * @author k.vagin
 */

class Utils {
	/**
	 * получить массив всех возможных булевских значений для N переменных (их ,как известно 2^N)
	 * @param int $count
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
} 