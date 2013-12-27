<?php
/**
 *
 * @author k.vagin
 */

class Heuristic {

	public static $sql_tokens = array(
		'select',
		'where',
		'from',
		'limit',
		'alter',
		'update',
		'remove',
		'insert',
		'table',
	);

	/**
	 * пытается угдать - похожа ли строка на sql запрос
	 * @param $string
	 * @return bool
	 */
	public static function is_maybe_sql_query($string)
	{
		$string = function_exists('mb_strtolower') ? mb_strtolower($string, 'UTF-8') : strtolower($string);
		$string = preg_replace('/\"(.+)\"/', '', $string);
		$string = preg_replace('/\'(.+)\'/', '', $string);
		$string = preg_replace('/\`(.+)\`/', '', $string);

		if (strpos($string, '<select') !== false) {
			return false;
		}

		$ar_words = preg_split('/[\s]/', $string);

		return count(array_intersect($ar_words, self::$sql_tokens)) >= 2;
	}
} 