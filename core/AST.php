<?php
namespace Core;
/**
 * операции с синтаксическим деревом
 * @author k.vagin
 */

class AST
{
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

		if (is_object($nodes)) {
			// раскрываем в массив все под деревья, содержащие AST
			$tmp_nodes = array();
			foreach ($nodes->getSubNodeNames() as $sub_node) {
				if (is_object($nodes->{$sub_node})) {
					$tmp_nodes[] = $nodes->{$sub_node};
				}
				elseif (is_array($nodes->{$sub_node})) {
					$tmp_nodes = array_merge($tmp_nodes, $nodes->{$sub_node});
				}
			}
			$nodes = $tmp_nodes;
		}
		elseif (!is_array($nodes)) {
			return array();
		}

		foreach ($nodes as $node) {

			if (is_object($node) && get_class($node) === $find_class_name) {
				$result[] = $node;
				if ($is_first) break;
			}

			if ($deep && !($node instanceof \PHPParser_Node_Scalar)) {
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
			return $nodes->getLine();
		}
		else {
			throw new \Exception("unknow node type");
		}
	}

	/**
	 * сравнивает 2 дерева с учетом порядка агрументов коммутативных операторов
	 * NB. не понимает много подряд идущих операторов, вида $a && $b && $c, в силу того, что операнды оказываются в разных поддеревьях
	 * @param \PHPParser_Node|\PHPParser_Node[] $tree_a
	 * @param $tree_b
	 * @return bool
	 */
	public static function compare_trees($tree_a, $tree_b)
	{
		foreach ($tree_a as $i => $node_a) {

			if (!array_key_exists($i, $tree_b)) {
				return false;
			}
			/** @var \PHPParser_Node $node_b */
			$node_b = $tree_b[$i];

			if (is_scalar($node_a)) {
				if (!is_scalar($node_b) || $node_b != $node_a) {
					return false;
				}

				continue;
			}

			// fixme вероятно проверка на массив не нужна
			/*if (is_array($node_a)) {
				if (!is_array($node_b) || (serialize($node_b) != serialize($node_a))) {
					return false;
				}

				continue;
			}*/

			if ($node_a === null && $node_b === null) {
				return true;
			}

			$sub_nodes = $node_a->getSubNodeNames();
			$t = array_diff_assoc($sub_nodes, $node_b->getSubNodeNames());

			if (!empty($t)) {
				return false;
			}

			$type = $node_a->getType();
			if ($type !== $node_b->getType()) {
				return false;
			}

			$identical = true;
			foreach ($sub_nodes as $sub_node_name) {

				if (is_scalar($node_a->{$sub_node_name})) {
					$identical = $node_a->{$sub_node_name} == $node_b->{$sub_node_name};
				}
				else {
					$identical = self::compare_trees(
						is_array($node_a->{$sub_node_name}) ? $node_a->{$sub_node_name} : array($node_a->{$sub_node_name}),
						is_array($node_b->{$sub_node_name}) ? $node_b->{$sub_node_name} : array($node_b->{$sub_node_name})
					);
				}

				if (!$identical) {
					break;
				}
			}

			// сравнение с учетом коммутативности операторов, в случае если прямое сравнение провалилось
			if (!$identical && in_array($type, \Core\Repository::$commutative_operators_Node_type)) {
				$identical = self::compare_trees(array($node_a->right), array($node_b->left));
				$identical = $identical && self::compare_trees(array($node_a->left), array($node_b->right));
			}

			if (!$identical) {
				return false;
			}
		}

		return true;
	}
} 