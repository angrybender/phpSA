<?php
/**
 * кеширует структуру вызовов и ответов всех пхп ф-ий из third_party/php_standards
 * @author k.vagin
 */

include __DIR__ .'/../../bootstrap.php';

$files = glob(__DIR__ .'/../../third_party/php_standards/*.php');

function get_return_from_phpdoc(\PHPParser_Comment_Doc $node)
{
	$result_types = array();
	$types = array(
		'mixed' => 'M',
		'int' => 'I',
		'float' => 'F',
		'string' => 'S',
		'bool' => 'B',
		'void' => 'V',
		'false' => 'B',
		'true' => 'B',
		'zero' => 'I',
		'array' => 'A',
		'resource' => 'R'
	);
	$text = $node->getText();
	if (preg_match("/\@return(.*)$/um", $text, $return))
	{
		$_types = preg_split('/[\s\|]/', $return[1]);
		foreach ($_types as $type) {
			if (isset($types[$type])) {
				$result_types[] = $types[$type];
				if (count($result_types) > 1) {
					break;
				}
			}
		}
	}

	return $result_types;
}

$all_functions = array();

foreach ($files as $file) {

	$tokens = \Core\Tokenizer::parse_file($file);

	try {
		$functions = \Core\AST::find_tree_by_root($tokens, 'PHPParser_Node_Stmt_Function');
		foreach ($functions as $func) {
			$name = $func->name;
			$phpDocs = $func->getDocComment();
			if ($phpDocs) {
				$params = array();
				$infinity_params_from = 0;
				foreach ($func->params as $i => $param) {
					if ($param->name === '_') {
						$infinity_params_from = $i+1;
					}
					$params[] = $param->byRef;
				}

				$all_functions[$name] = array(
					'ret' => get_return_from_phpdoc($phpDocs),
					'args' => $params,
					'ipf' => $infinity_params_from
				);
			}
		}
	}
	catch (\Exception $e) {
		echo 'error: ', $file, PHP_EOL;
		die();
	}

	echo '.';
}
echo PHP_EOL;

file_put_contents(__DIR__ . '/../../' . \Analisator\Config::$cache_path . '/php_standards_functions.txt', serialize($all_functions));