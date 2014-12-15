<?php
/**
 *
 * @author k.vagin
 */

namespace Core\Flow\Trace;


class Cache
{
	private static $cache = array();

	/**
	 * @param array $data
	 * @param $key
	 * @param string $section
	 */
	public static function setCache($data, $key, $section)
	{
		self::$cache[$section][$key] = $data;
	}

	public static function addCacheIsExist($data, $key, $section)
	{
		$exist_data = self::getCache($key, $section);
		if (!empty($exist_data)) {
			self::$cache[$section][$key][] = $data;
		}
		else {
			self::$cache[$section][$key] = array($data);
		}
	}

	/**
	 * @param $key
	 * @param string $section
	 * @return array
	 */
	public static function getCache($key, $section)
	{
		if (!isset(self::$cache[$section]) || !isset(self::$cache[$section][$key])) {
			return null;
		}

		return self::$cache[$section][$key];
	}

	/**
	 * кеширование типа данных, который возвращает ф-ия
	 * @param array $name_space
	 * @param $name
	 * @param Variable[] $params
	 * @param Variable $return_type
	 */
	public static function setFunction(array $name_space, $name, $params, Variable $return_type)
	{
		$main_key = join('/', $name_space) . '//' . $name;
		$section = 'function_cache';

		self::addCacheIsExist(array(
			'params' => $params,
			'return' => $return_type
		), $main_key ,$section);

		// алиасы для нечеткого поиска:
		$key = array();
		array_pop($name_space); // удаляем последний элемент, так надо
		foreach ($name_space as $ns) {
			$key[] = $ns;
			$cache_key = join('/', $key) . '//' . $name . '*';

			$links = self::getCache($cache_key, $section);
			// избегаем дублирования ключей
			if ($links && !in_array($main_key, $links) || empty($links)) {
				self::addCacheIsExist($main_key, $cache_key, $section);
			}
		}
	}

	/**
	 * @param array $name_space
	 * @param $name
	 * @param null $arg_count
	 * @param bool $similar если истина - поиск нечеткий, т.к может искать по неполному неймспейсу
	 * @return array
	 */
	public static function getFunction(array $name_space, $name, $arg_count = null, $similar = false)
	{
		if (empty($name_space)) {
			$name_space = array('/');
		}

		$main_key = join('/', $name_space) . '//' . $name;
		$section = 'function_cache';

		$function_dscr = self::getCache($main_key, $section);
		if (!empty($function_dscr) && !empty($arg_count)) {
			// дополнительно сравниваем по количеству аргументов
			$filtered = array();
			foreach ($function_dscr as $function) {
				if (count($function['params']) === $arg_count) {
					$filtered[] = $function;
				}
			}

			$function_dscr = $filtered;
		}

		if ($similar && empty($function_dscr)) {
			// нечеткий поиск по частичному совпадению неймспейсов
			$function_dscr = array();
			$keys = array();
			$key = array();
			foreach ($name_space as $ns) {
				$key[] = $ns;
				$keys[] = join('/', $key) . '//' . $name . '*';
			}
			$keys = array_reverse($keys);

			foreach ($keys as $cache_key) {
				$link_to_key = self::getCache($cache_key, $section);
				foreach ($link_to_key as $link_to_key_item) {
					$function_dscr = array_merge($function_dscr, self::getCache($link_to_key_item, $section));
				}

				if (!empty($function_dscr)) {
					break;
				}
			}

			// дополнительно сравниваем по количеству аргументов
			if (!empty($arg_count)) {
				$filtered = array();
				foreach ($function_dscr as $function) {
					if (count($function['params']) === $arg_count) {
						$filtered[] = $function;
					}
				}

				$function_dscr = $filtered;
			}
		}

		return $function_dscr;
	}
}