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
	 * @param string|array $find_class_name имя класса ноды, которую ищет
	 * @param bool $deep если ложь то возвращает без рекурсии
	 * @param bool $is_first если истина то возращает первый найденный
	 * @param int $recursion_deep флаг для контроля глубины рекурсии
	 * @throws \Exception
	 * @return \PHPParser_Node[]
	 */
	public static function find_tree_by_root($nodes, $find_class_name, $deep = true, $is_first = false, $recursion_deep = 0)
	{
		if ($recursion_deep > 1500) {
			// необходимый костыль ограничения глубины рекурсивного вызова
			throw new \Exception("Can't find subtree - too deep recursion");
		}

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

			if (is_object($node)) {
				$node_class = get_class($node);
				if ($node_class === $find_class_name) {
					$result[] = $node;
					if ($is_first) break;
				}
				elseif (is_array($find_class_name) && in_array($node_class, $find_class_name)) {
					$result[] = $node;
					if ($is_first) break;
				}
			}

			if ($deep && !($node instanceof \PHPParser_Node_Scalar)) {
				$result = array_merge($result, self::find_tree_by_root($node, $find_class_name, $recursion_deep+1));
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
	 * @param \PHPParser_Node|\PHPParser_Node[] $tree_a
	 * @param $tree_b
	 * @param bool $strict
	 * @return bool
	 */
	public static function compare_trees($tree_a, $tree_b, $strict = false)
	{
		foreach ($tree_a as $i => $node_a) {

			if (!array_key_exists($i, $tree_b)) {
				return false;
			}
			/** @var \PHPParser_Node $node_b */
			$node_b = $tree_b[$i];

			if (!$strict) {
				$node_a = ($node_a instanceof \PHPParser_Node) ? self::tree_sort($node_a) : $node_a;
				$node_b = ($node_b instanceof \PHPParser_Node) ? self::tree_sort($node_b) : $node_b;
			}

			if (is_scalar($node_a)) {
				if (!is_scalar($node_b) || $node_b != $node_a) {
					return false;
				}

				continue;
			}

			if ($node_a === null) {
				if ($node_b === null) {
					return true;
				}
				else {
					return false;
				}
			}

			if ($node_b === null || !is_object($node_b)) {
				return false;
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
						is_array($node_b->{$sub_node_name}) ? $node_b->{$sub_node_name} : array($node_b->{$sub_node_name}),
						$strict
					);
				}

				if (!$identical) {
					break;
				}
			}

			if (!$identical) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param \PHPParser_Node $tree
	 * @return \PHPParser_Node
	 */
	public static function tree_sort(\PHPParser_Node $tree)
	{
		$tree = clone $tree;
		$tree->unsetAttributes(); // удаляем аттрибуты перед сравнением

		if (in_array($tree->getType(), \Core\Repository::$commutative_operators_Node_type)) {
			while (true) {
				$left = self::tree_sort($tree->left);
				$right = self::tree_sort($tree->right);

				$sorted_tree = self::subtree_sort($left, $right, $tree);

				if (Tokenizer::printer($tree) === Tokenizer::printer($sorted_tree)) {
					// если очередная итерация сортировки не вызывала перемещение узлов дерева, выход
					break;
				}

				$tree = $sorted_tree;
			}

			return $tree;
		}
		else {
			$sub_nodes = $tree->getSubNodeNames();
			foreach ($sub_nodes as $node) {
				if ($tree->{$node} instanceof \PHPParser_Node) {
					$tree->{$node} = self::tree_sort($tree->{$node});
				}
				elseif (is_array($tree->{$node})) {
					foreach ($tree->{$node} as $i => $sub_node) {
						if ($sub_node instanceof \PHPParser_Node) $tree->{$node}[$i] = self::tree_sort($sub_node);
					}
				}
			}
		}

		return $tree;
	}

	/**
	 * сортировка с анализом в глубину по 1 поддерево
	 * левое и правое поддерево уже отсортировано
	 * @param \PHPParser_Node $left
	 * @param \PHPParser_Node $right
	 * @param \PHPParser_Node $parent
	 */
	protected static function subtree_sort(\PHPParser_Node $left, \PHPParser_Node $right, \PHPParser_Node $parent)
	{
		$parent_type = $parent->getType();
		$parent_class = get_class($parent);

		$left_type = $left->getType();
		$right_type = $right->getType();

		$right_code = Tokenizer::printer($right);
		$left_code = Tokenizer::printer($left);

		$left_right = array($left, $right);

		if ($left_type === $parent_type) {
			$left_left_code = Tokenizer::printer($left->left);
			$left_right_code = Tokenizer::printer($left->right);

			if ($right_code < $left_left_code) {
				// меняем правое поддерево на левое поддерево левого поддерева
				$left_right = self::swipe_nodes($left, $right, 'left', null);
			}
			elseif ($right_code < $left_right_code) {
				$left_right = self::swipe_nodes($left, $right, 'right', null);
			}
		}

		if ($right_type === $parent_type) {
			$right_left_code = Tokenizer::printer($right->left);
			$right_right_code = Tokenizer::printer($right->right);

			if ($left_code < $right_left_code) {
				$left_right = self::swipe_nodes($left, $right, null, 'left');
			}
			elseif ($left_code < $right_right_code) {
				$left_right = self::swipe_nodes($left, $right, null, 'right');
			}
		}

		if ($left_code > $right_code) {
			$left_right = array($right, $left);
		}

		return new $parent_class($left_right[0], $left_right[1]);
	}

	/**
	 * меняет местами ноды в деревьях
	 * @param \PHPParser_Node $destination
	 * @param \PHPParser_Node $source
	 * @param string $destination_position
	 * @param string $source_position left|right|null
	 * @return \PHPParser_Node[] array($destination, $source)
	 */
	public static function swipe_nodes(\PHPParser_Node $destination, \PHPParser_Node $source, $destination_position = 'left', $source_position = 'left')
	{
		if ($source_position === null && $destination_position === null) {
			return array(clone $source, clone $destination);
		}

		$source_right = null;
		$destination_right = null;
		if ($source_position === 'left' && $destination_position === 'left') {
			$destination_left = clone $source->left;
			$destination_right  = clone $destination->right;

			$source_left = clone $destination->left;
			$source_right = clone $source->right;
		}

		if ($source_position === 'left' && $destination_position === 'right') {
			$destination_left = clone $destination->left;
			$destination_right = clone $source->left;

			$source_left = clone $destination->right;
			$source_right = clone $source->right;
		}

		if ($source_position === 'right' && $destination_position === 'right') {
			$destination_left = clone $destination->left;
			$destination_right = clone $source->right;

			$source_left = clone $source->left;
			$source_right = clone $destination->right;
		}

		if ($source_position === 'right' && $destination_position === 'left') {
			$destination_left = clone $source->right;
			$destination_right  = clone $destination->right;

			$source_left = clone $source->left;
			$source_right = clone $destination->left;
		}

		if ($source_position === null && $destination_position === 'left') {
			$destination_left = clone $source;
			$destination_right  = clone $destination->right;

			$source_left = clone $destination->left;
		}

		if ($source_position === null && $destination_position === 'right') {
			$destination_left = clone $destination->left;
			$destination_right = clone $source;

			$source_left = clone $destination->right;
		}

		if ($source_position === 'left' && $destination_position === null) {
			$destination_left = clone $source->left;

			$source_left = clone $destination;
			$source_right = clone $source->right;
		}

		if ($source_position === 'right' && $destination_position === null) {
			$destination_left = clone $source->right;

			$source_left = clone $source->left;
			$source_right = clone $destination;
		}

		$source_class = get_class($source);
		$destination_class = get_class($destination);

		if ($source_right !== null) {
			$source = new $source_class($source_left, $source_right);
		}
		else {
			$source = $source_left;
		}

		if ($destination_right !== null) {
			$destination = new $destination_class($destination_left, $destination_right);
		}
		else {
			$destination = $destination_left;
		}

		return array($destination, $source);
	}

	/**
	 * @param \PHPParser_Node|\PHPParser_Node[] $nodes
	 * @param \PHPParser_Node $needle_tree
	 * @param bool $is_first
	 * @return \PHPParser_Node[]
	 */
	public static function find_subtrees($nodes, \PHPParser_Node $needle_tree, $is_first = false)
	{
		$class = get_class($needle_tree);
		$preliminaries = self::find_tree_by_root($nodes, $class);

		$result = array();
		foreach ($preliminaries as $preliminary) {
			if (self::compare_trees(array($preliminary), array($needle_tree), true)) {
				$result[] = $preliminary;
				if ($is_first) {
					break;
				}
			}
		}

		return $result;
	}
} 