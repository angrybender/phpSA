<?php
namespace Core\Flow;
use Core\Tokenizer;

/**
 * поток выполнения под программы
 * @author k.vagin
 */


class FlowIf
{
	/**
	 * переопределенные выше операнды (нужно когда между двумя if-ами один из операндов переопределяется)
	 * @var array
	 */
	protected $scope = array();

	/**
	 * список условий, истинных в данном потоке исполнения
	 * формат
	 * @var \PHPParser_Node[]
	 */
	protected $conditions = array();

	/**
	 * обнаруженные противоречия условий
	 * @var array
	 */
	protected $errors = array();

	/**
	 * можно пробросить предвычесленные ограничения переменных
	 * можно пробросить переопределенные выше операнды
	 * @param null | \PHPParser_Node[] $nodes
	 * @param array $scope
	 * @param array $conditions
	 */
	public function __construct($nodes = null, array $scope = array(), array $conditions = array())
	{
		$this->scope = $scope;
		$this->conditions = $conditions;

		if ($nodes) {
			$this->trace($nodes);
		}
	}

	/**
	 * проверяет на взаимную не противоречивость выржения в проперти conditions и переданное выражение
	 * @param \PHPParser_Node $tree
	 * @throws \Exception
	 * @return \Exception
	 */
	protected function check_conditions(\PHPParser_Node $tree)
	{
		foreach ($this->conditions as $condition) {
			try {
				Solver::estimate_on_boolean_collision(new \PHPParser_Node_Expr_BooleanAnd($tree, $condition));
			}
			catch (\Exception $e) {
				if ($e instanceof \Core\Flow\Exceptions\ExprFalse || $e instanceof \Core\Flow\Exceptions\ExprTrue || $e instanceof \Core\Flow\Exceptions\ExprEq) {
					return array(Tokenizer::printer($tree), Tokenizer::printer($condition));
				}
				else {
					throw $e;
				}
			}
		}

		return null;
	}

	/**
	 * запускает новый поток "выполнения"
	 * @param \PHPParser_Node $condition
	 * @param  \PHPParser_Node $stmts
	 */
	protected function generate_new_flow($condition, $stmts)
	{
		$cond = Tokenizer::parser('<?php ' . Solver::boolean_tree_optimize($condition) . ';');

		$check = $this->check_conditions($cond[0]);

		if ($check !== null) {
			// проверяем не участвует ли в выражении какой либо операнд. который был переопределен выше
			foreach ($this->scope as $asset_node) {
				$is_break = false;
				$found = \Core\AST::find_tree_by_root($cond, get_class($asset_node));
				foreach ($found as $f_node) {
					if (\Core\AST::compare_trees(array($f_node), array($asset_node))) {
						$check = null;
						$is_break = true;
						break;
					}
				}

				if ($is_break) {
					break;
				}
			}
		}

		if ($check === null) {
			$flow = new FlowIf($stmts, $this->scope, array_merge($this->conditions, $cond));
			$this->errors = array_merge($this->errors, $flow->getErrors());
		}
		else {
			$this->errors[] = array(
				'message' 	=> $check,
				'line'		=> $condition->getLine()
			);
		}
	}

	/**
	 * "выполняет код", возвращая все возможные состояния
	 * @param \PHPParser_Node[] $nodes
	 */
	public function trace($nodes)
	{
		foreach ($nodes as $tree) {

			if (!is_object($tree)) {
				continue;
			}

			$type = $tree->getType();
			if ($type === 'Expr_Assign' && !empty($this->conditions)) {
				$this->scope[] = $tree->var;
			}
			elseif ($type == 'Stmt_If') {
				// для каждой альтернативы просчитываем свой поток выполнения:
				$this->generate_new_flow($tree->cond, $tree->stmts);

				if ($tree->else) {
					$this->generate_new_flow(new \PHPParser_Node_Expr_BooleanNot($tree->cond), $tree->else->stmts);
				}

				foreach ($tree->elseifs as $elseif_node) {
					$this->generate_new_flow($elseif_node->cond, $elseif_node->stmts);
				}

				$this->conditions = array();
			}
		}
	}

	public function getErrors()
	{
		return $this->errors;
	}
}