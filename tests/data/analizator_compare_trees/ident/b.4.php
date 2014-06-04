<?php
/**
 * сравнивает 2 дерева с учетом порядка агрументов тех операторов, для которых порядок не важен
 * @param \PHPParser_Node|\PHPParser_Node[] $tree_a
 * @param $tree_b
 * @return bool
 */
function compare_trees($tree_a, $tree_b)
{
	foreach ($tree_a as $i => $node_a) {

		if (!isset($tree_b[$i])) {
			return false;
		}
		/** @var \PHPParser_Node $node_b */
		$node_b = $tree_b[$i];

		if (is_scalar($node_a)) {
			if (!is_scalar($node_b) || $node_a != $node_b) { // diff 1
				return false;
			}

			continue;
		}

		if (is_array($node_a)) {
			if ((serialize($node_b) != serialize($node_a)) || !is_array($node_b)) { // diff 2
				return false;
			}

			continue;
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
			$identical = self::compare_trees(array($node_a->{$sub_node_name}), array($node_b->{$sub_node_name}));
			if (!$identical) {
				break;
			}
		}

		// сравнение с учетом коммутативности операторов, в случае если прямое сравнение провалилось
		if (!$identical && isset(\Core\Repository::$commutative_operators_Node_type[$type])) {
			if (!self::compare_trees(array($node_a->right), array($node_b->left))) {
				return false;
			}

			if (!self::compare_trees(array($node_a->left), array($node_b->right))) {
				return false;
			}
		}
	}

	return true;
}