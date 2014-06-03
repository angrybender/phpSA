<?php
namespace Core;
/**
 * операции с синтаксическим деревом
 * @author k.vagin
 */

class AST {

	/**
	 * ищет заданное поддерево по имени класса корневого узла искомого поддерева
	 * @param $nodes
	 * @param $find_class_name имя класса ноды, которую ищет
	 * @param bool $deep если ложь то возвращает без рекурсии
	 * @param bool $is_first если истина то возращает первый найденный
	 * @return \PHPParser_Node[]
	 */
	public static function find_tree_by_root($nodes, $find_class_name, $deep = true, $is_first = false)
	{
		$result = array();

		if (is_object($nodes) && isset($nodes->stmts)) {
			return self::find_tree_by_root($nodes->stmts, $find_class_name);
		}
		elseif (!is_array($nodes)) {
			return array();
		}

		foreach ($nodes as $node) {

			if (is_object($node) && get_class($node) === $find_class_name) {
				$result[] = $node;
				if ($is_first) break;
			}

			if ($deep && !is_scalar($node) && !($node instanceof \PHPParser_Node_Scalar)) {
				$result = array_merge($result, self::find_tree_by_root($node, $find_class_name));
				if ($is_first) break;
			}
		}

		return $result;
	}

	/**
	 * возвращает аттрибут номер строки у головы переданного дерева
	 * @param $nodes
	 * @throws \Exception
	 * @return int
	 */
	public static function get_line_of_tree($nodes)
	{
		if (is_array($nodes)) {
			$nodes = $nodes[0];
		}

		if ($nodes instanceof \PHPParser_NodeAbstract) {
			return $nodes->getAttribute('startLine');
		}
		else {
			throw new \Exception("unknow node type");
		}
	}
} 