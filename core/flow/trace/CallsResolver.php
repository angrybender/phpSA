<?php
/**
 * сохранить вызовы функций, классов, методов
 * найти функцию, метод, класс и тд
 * @author k.vagin
 */

namespace Core\Flow\Trace;


class CallsResolver
{
	/**
	 * сохранение определения функции
	 * @param array $name_space
	 * @param $name
	 * @param Variable[] $params
	 * @param Variable $return_type
	 */
	public static function setFunction(array $name_space, $name, $params, Variable $return_type)
	{
		$main_key = self::getCacheKeyForFunction($name_space, $name);
		$similar_key = self::getCacheKeyForSimilarSearch($name, 'null'); // для нечеткого поиска:

		Cache::addCacheIsExist(new FunctionCallDescription($name_space, $params, $return_type), $main_key, 'function_cache');

		// для нечеткого поиска:
		Cache::addCacheIsExist($main_key, $similar_key, 'function_similar_cache');
	}

	/**
	 * сохранение определения метода класса
	 * @param array $name_space
	 * @param $class_name
	 * @param $name
	 * @param $params
	 * @param Variable $return_type
	 */
	public static function setMethod(array $name_space, $class_name, $name, $params, Variable $return_type)
	{
		$main_key = self::getCacheKeyForMethod($name_space, $name, $class_name);
		$similar_key = self::getCacheKeyForSimilarSearch($name, $class_name); // для нечеткого поиска:

		Cache::addCacheIsExist(new MethodCallDescription($name_space, $class_name, $params, $return_type), $main_key, 'method_cache');

		// для нечеткого поиска:
		Cache::addCacheIsExist($main_key, $similar_key, 'method_similar_cache');
		Cache::addCacheIsExist($main_key, self::getCacheKeyForSimilarSearch($name, '*'), 'method_similar_cache'); // для совсем тяжелых случаев
	}

	/**
	 * сохранение описания класса
	 * @param array $name_space
	 * @param $name
	 */
	public static function setClass(array $name_space, $name)
	{
		$main_key = self::getCacheKeyForClass($name_space, $name);
		$similar_key = self::getCacheKeyForSimilarSearch($name, 'class'); // для нечеткого поиска:

		Cache::addCacheIsExist(new ClassDescription($name_space, $name), $main_key, 'class_cache');

		// для нечеткого поиска:
		Cache::addCacheIsExist($main_key, $similar_key, 'class_similar_cache');
	}

	/**
	 * @param array $name_space
	 * @param $name
	 * @param bool $similar
	 * @return FunctionCallDescription[]
	 */
	public static function getFunction(array $name_space, $name, $similar = false)
	{
		$main_key = self::getCacheKeyForFunction($name_space, $name);
		$similar_key = self::getCacheKeyForSimilarSearch($name, 'null');

		$functions = Cache::getCache($main_key, 'function_cache');

		if (empty($functions) && $similar) {
			$functions_by_name = self::getCacheByLink($similar_key, 'function_similar_cache', 'function_cache');

			// проверяем по совпадению неймеспейсов: если все нейспесы, переданные в данный метод присутствуют в описании функции из кеша - забираем ее
			$functions = self::FilterSearchedByNamespaces($name_space, $functions_by_name);
		}

		return $functions;
	}

	/**
	 * @param array $name_space
	 * @param $class_name
	 * @param $name
	 * @param bool $similar
	 * @return MethodCallDescription[]
	 */
	public static function getMethod(array $name_space, $class_name, $name, $similar = false)
	{
		$main_key = self::getCacheKeyForMethod($name_space, $name, $class_name);
		$similar_key = self::getCacheKeyForSimilarSearch($name, $class_name);

		$methods = Cache::getCache($main_key, 'method_cache');

		$similar_searched = array();
		if (empty($methods) && $similar) {
			$similar_searched = self::getCacheByLink($similar_key, 'method_similar_cache', 'method_cache');
		}

		if (empty($similar_searched) && $similar) {
			$similar_searched = Cache::getCache(self::getCacheKeyForSimilarSearch($name, '*'), 'method_similar_cache');
		}

		// проверяем по совпадению неймеспейсов: если все нейспесы, переданные в данный метод присутствуют в описании функции из кеша - забираем ее
		if (!empty($similar_searched)) {
			$methods = self::FilterSearchedByNamespaces($name_space, $similar_searched);
		}

		return $methods;
	}

	/**
	 * @param array $name_space
	 * @param $name
	 * @param bool $similar
	 * @return ClassDescription[]
	 */
	public static function getClass(array $name_space, $name, $similar = false)
	{
		$main_key = self::getCacheKeyForClass($name_space, $name);
		$similar_key = self::getCacheKeyForSimilarSearch($name, 'class');

		$classes = Cache::getCache($main_key, 'class_cache');

		if (empty($classes) && $similar) {
			$similar_classes = self::getCacheByLink($similar_key, 'class_similar_cache', 'class_cache');
			$classes = self::FilterSearchedByNamespaces($name_space, $similar_classes);
		}

		return $classes;
	}

	/**
	 * @param array $name_space
	 * @param $name
	 * @return string
	 */
	protected static function getCacheKeyForFunction(array $name_space, $name)
	{
		return join('/', $name_space) . '//' . $name;
	}

	/**
	 * @param array $name_space
	 * @param $name
	 * @param $class_name
	 * @return string
	 */
	protected static function getCacheKeyForMethod(array $name_space, $name, $class_name)
	{
		return join('/', $name_space) . '/' . $class_name . '::' . $name;
	}

	/**
	 * @param array $name_space
	 * @param $name
	 * @return string
	 */
	protected static function getCacheKeyForClass(array $name_space, $name)
	{
		return join('/', $name_space) . '/' . $name;
	}

	/**
	 * @param $name
	 * @param $section
	 * @return string
	 */
	protected static function getCacheKeyForSimilarSearch($name, $section)
	{
		return $section . '::' . $name;
	}

	/**
	 * фильтрует переданные объекты-описания по немспейскам
	 * @param array $name_space
	 * @param $searched_items
	 * @return array
	 */
	protected static function FilterSearchedByNamespaces(array $name_space, $searched_items)
	{
		if (empty($searched_items)) {
			return array();
		}

		$filtered_items = array();

		/** @var FunctionCallDescription|MethodCallDescription $fn */
		foreach ($searched_items as $fn) {
			if (count(array_intersect($name_space, $fn->getNameSpace())) === count($name_space)) {
				$filtered_items[] = $fn;
			}
		}
		return $filtered_items;
	}

	/**
	 * возвращает из кеша элементы по ссылкам на них из другого элемента кеша
	 * @param $key
	 * @param $section_link
	 * @param $section
	 * @return array
	 */
	protected static function getCacheByLink($key, $section_link, $section)
	{
		$links = array_unique(Cache::getCache($key, $section_link));
		if (empty($links)) {
			return array();
		}

		$data = array();
		foreach ($links as $link) {
			$data = array_merge($data, Cache::getCache($link, $section));
		}

		return array_filter($data);
	}
}