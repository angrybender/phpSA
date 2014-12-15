<?php
namespace Core;
/**
 *
 * @author k.vagin
 */

class Tokenizer
{
	protected static $file_cache = array();

	/**
	 * @param $php_text
	 * @return \PHPParser_Node[]
	 */
	public static function parser($php_text)
	{
		$parser = new \PHPParser_Parser(new \PHPParser_Lexer);
		return $parser->parse($php_text);
	}

	/**
	 * @param $file_name
	 * @return \PHPParser_Node[]
	 */
	public static function parse_file($file_name)
	{
		if (!isset(self::$file_cache[$file_name])) {
			try {
				self::$file_cache[$file_name] = self::parser(file_get_contents($file_name));
			}
			catch (\PHPParser_Error $e) {
				self::$file_cache[$file_name] = $e;
			}
		}

		return self::$file_cache[$file_name];
	}

	public static function printer($nodes)
	{
		$printer = new \PHPParser_PrettyPrinter_Default();
		$expression = $printer->prettyPrint(array($nodes));
		if (substr($expression, 0, 5) === '<?php') {
			$expression = substr($expression, 6); // отрезаем <?php
		}

		if (substr($expression, -1, 1) === ';') {
			return substr($expression, 0, -1);
		}
		else {
			return $expression;
		}
	}

	public static function cleanPrinter($nodes)
	{
		$nodes = clone $nodes;
		$nodes->setAttribute('comments', array());
		$nodes = AST::walk($nodes, function($node) {
			$node->setAttribute('comments', array());
			return $node;
		});

		return self::printer($nodes);
	}

	/**
	 * присваивает каждой ноде id
	 * @param \PHPParser_Node[]| \PHPParser_Node $nodes
	 */
	public static function enumerate($nodes)
	{

	}
} 