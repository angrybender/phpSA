<?php
/**
 *
 * @author k.vagin
 */
function  __construct()
{
	$constraints = &$this->constraints;

	$this->check = function(&$array) use (&$constraints) {
		$count = count($array);

		while (($item = each($array)) && (list(,$check) = each($constraints))) {
			if (!$check($item, $count)) {
				list($key, $value) = $item;
				throw new Exception('Filed check of '.$key.' => ' .
					(method_exists('PHPUnit_Util_Type', 'toString') ?
						PHPUnit_Util_Type::toString($value) : PHPUnit_Util_Type::export($value)));
			}
		}
		return true;
	};

	// проверяет начинающуюся с 0 последовательность ключей
	$this->check_list = function($item) {
		$array = array_keys($item);

		return count($array) == 1
		&& reset($array) === 0
		|| array_reduce(array_map(function($a1, $b1) {
				return ($a1 == $b1 - 1);
			}, array_slice($array, 0, count($array) - 1), array_slice($array, 1)), function($result, $item) {
				return $result && $item;
			}, reset($array) === 0);
	};
}