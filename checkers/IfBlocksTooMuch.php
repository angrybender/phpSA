<?php
/**
 * очень много вложенных if-ов путает код
 * @author k.vagin
 */

namespace Checkers;


class IfBlocksTooMuch extends \Analisator\ParentChecker
{
	protected $types = array(
		CHECKER_HEURISTIC
	);

	protected $error_message = 'Слишком большое количество вложенных If';

	private $max = 3;			// больше этого значения вложенных if - ошибка
	private $line_distance = 3; // учитывать только если не больше стольки строк между началами вложенных блоками

	const
		NODE_NAME = 'PHPParser_Node_Stmt_If';

	protected function check($nodes)
	{
		$if_nodes = \Core\AST::find_tree_by_root($nodes, self::NODE_NAME);
		foreach ($if_nodes as $if_root) {
			if ($this->check_tree($if_root, 0) + 1 > $this->max) {
				$this->set_error(\Core\AST::get_line_of_tree($if_root));
			}
		}
	}

	protected function check_tree($if_root, $deep)
	{
		$start_line = \Core\AST::get_line_of_tree($if_root);

		$if_sub_tree = \Core\AST::find_tree_by_root($if_root, self::NODE_NAME, false);
		$nested_deep = array($deep);
		foreach ($if_sub_tree as $if_root) {
			if (\Core\AST::get_line_of_tree($if_root) - $start_line > $this->line_distance) {
				// если вложенные блоки далеко друг от друга - пропускаем
				continue;
			}

			$nested_deep[] = $this->check_tree($if_root, $deep+1);
		}

		return max($nested_deep);
	}
}