<?php
/**
 * @author k.vagin
 */

namespace Core\Flow;


class Solver
{
	// исключения для self::boolean_tree_optimize
	protected static $not_operand_expressions = array(
		'Expr_Variable',
		'Expr_FuncCall',
		'Expr_PropertyFetch',
		'Expr_MethodCall',
		'Expr_BooleanNot',
	);

	/**
	 * преобразует выражение NOT A*, удаляя NOT
	 * @param \PHPParser_Node $tree $tree
	 * @return \PHPParser_Node
	 */
	protected static function reverse_logical_operator($tree)
	{
		$type = $tree->getType();
		if ($type === 'Expr_BooleanNot') {
			return $tree->expr;
		}

		if ($type === 'Expr_BooleanAnd' || $type === 'Expr_BooleanOr') {
			$left = new \PHPParser_Node_Expr_BooleanNot($tree->left);
			$right = new \PHPParser_Node_Expr_BooleanNot($tree->right);


			$negative_class = "\\PHPParser_Node_" . \Core\Repository::$reverse_operators_rules[$type];
			return new $negative_class($left, $right);
		}

		if (isset(\Core\Repository::$reverse_operators_rules[$type])) {
			// остались всякие операторы сравнения
			$negative_class = "\\PHPParser_Node_" . \Core\Repository::$reverse_operators_rules[$type];
			return new $negative_class($tree->left, $tree->right);
		}

		return false;
	}

	/**
	 * оптимизирует булевское выражение по правилам Де-Моргана
	 * @see http://ru.wikipedia.org/wiki/%D0%97%D0%B0%D0%BA%D0%BE%D0%BD%D1%8B_%D0%B4%D0%B5_%D0%9C%D0%BE%D1%80%D0%B3%D0%B0%D0%BD%D0%B0
	 * @param \PHPParser_Node $tree
	 * @return bool | string
	 */
	public static function boolean_tree_optimize($tree)
	{
		$type = $tree->getType();

		$sub_expression = false;
		if ($type === 'Expr_BooleanNot') {
			// оператор отрицания внутрь скобок
			$reverse = self::reverse_logical_operator($tree->expr);
			if ($reverse) {
				$sub_expression = self::boolean_tree_optimize($reverse);
			}
		}

		if ($type === 'Expr_BooleanOr' || $type === 'Expr_BooleanAnd') {
			$left_expression = self::boolean_tree_optimize($tree->left);
			if ($left_expression === false) {
				return false;
			}

			$right_expression = self::boolean_tree_optimize($tree->right);
			if ($right_expression === false) {
				return false;
			}

			$sub_expression = "({$left_expression}" .
									($type === 'Expr_BooleanOr' ? ' || ' : ' && ') .
								"{$right_expression})";
		}

		if ($sub_expression === false) {
			return \Core\Tokenizer::printer($tree);
		}

		return $sub_expression;
	}

	/**
	 * сравнивает 2 дерева, но применительно к методу self::estimate_on_boolean_collision
	 * а именно, если даны деревья с оператором сравнения. проверяет на противоречивость
	 * @param \PHPParser_Node $tree_a
	 * @param \PHPParser_Node $variable
	 * @param $expr_type
	 * @throws Exceptions\ExprEq
	 * @return bool
	 */
	protected static function estimate_on_boolean_collision_compare_trees($tree_a, $variable, $expr_type)
	{
		$type = $tree_a->getType();

		if ($type !== $variable->getType()) {
			return false;
		}

		if ($expr_type === 'Expr_BooleanOr' || !in_array($type, \Core\Repository::$compare_eq_operators_Node_type)) {
			return \Core\AST::compare_trees(array($tree_a), array($variable));
		}

		// ищем совпадение по любому операнду оператора сравнения
		$tree_a_side = 'left';
		$is_eq = \Core\AST::compare_trees(array($tree_a->left), array($variable->left));
		if (!$is_eq) {
			$is_eq = \Core\AST::compare_trees(array($tree_a->right), array($variable->left));
			$tree_a_side = 'right';
		}

		if (!$is_eq) {
			$is_eq = \Core\AST::compare_trees(array($tree_a->right), array($variable->right));
			$tree_a_side = 'right';
		}

		if (!$is_eq) {
			$is_eq = \Core\AST::compare_trees(array($tree_a->left), array($variable->right));
			$tree_a_side = 'left';
		}

		if (!$is_eq) {
			// точно не равны
			return false;
		}

		// выбираем противоположные ветви для сравнения неодинаковых операндов
		$tree_a_neg_side = ($tree_a_side === 'left') ? 'right' : 'left';
		$tree_b_neg_side = $tree_a_side;

		if (!\Core\AST::compare_trees(array($tree_a->{$tree_a_neg_side}), array($variable->{$tree_b_neg_side}))) {
			throw new Exceptions\ExprEq(\Core\Tokenizer::printer($variable) . ' AND ' . \Core\Tokenizer::printer($tree_a));
		}
	}

	/**
	 * проверяет не является ли выражение булевским тождеством
	 * @param \PHPParser_Node $tree
	 * @param string $expr_type ищем по ветке && или ||
	 * @param \PHPParser_Node $variable переменная
	 * @param \PHPParser_Node $source_tree
	 * @return bool
	 * @throws \Exception
	 */
	public static function estimate_on_boolean_collision(\PHPParser_Node &$tree, $expr_type = "", $variable = null, &$source_tree = null)
	{
		if (empty($source_tree)) {
			$source_tree = $tree;
		}

		$type = $tree->getType();
		if ($type !== 'Expr_BooleanOr' && $type !== 'Expr_BooleanAnd') {

			if (!empty($variable) && self::estimate_on_boolean_collision_compare_trees($tree, $variable, $expr_type)) {
				if ($expr_type === 'Expr_BooleanOr') {
					throw new Exceptions\ExprTrue(\Core\Tokenizer::printer($variable->expr));
				}
				elseif ($expr_type === 'Expr_BooleanAnd') {
					throw new Exceptions\ExprFalse(\Core\Tokenizer::printer($variable->expr));
				}
				else {
					throw new \Exception("error");
				}
			}

			if (empty($variable)) {

				if (in_array($tree->getType(), \Core\Repository::$compare_eq_operators_Node_type)) {
					$_s_tree = clone $source_tree;
					$_tree = clone $tree;
					$tree = null;
					self::estimate_on_boolean_collision($source_tree, $expr_type, $_tree, $source_tree);

					$tree = clone $_tree;
					$source_tree = clone $_s_tree;
				}

				$variable = new \PHPParser_Node_Expr_BooleanNot($tree);
				return self::estimate_on_boolean_collision($source_tree, $expr_type, $variable, $source_tree);
			}

			return true;
		}

		if (empty($expr_type) && $type !== 'Expr_BooleanNot') {
			return self::estimate_on_boolean_collision($tree, $type, $variable, $tree);
		}

		if ($type === $expr_type) {
			if ($tree->left) self::estimate_on_boolean_collision($tree->left, $expr_type, $variable, $source_tree);
			if ($tree->right) self::estimate_on_boolean_collision($tree->right, $expr_type, $variable, $source_tree);
			return true;
		}

		return false;
	}
} 