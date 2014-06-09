<?php
namespace Core;
/**
 *
 * @author k.vagin
 */

class Tokenizer {

	/**
	 * @param $php_text
	 * @return \PHPParser_Node[]
	 */
	public static function parser($php_text)
	{
		$parser = new \PHPParser_Parser(new \PHPParser_Lexer);
		return $parser->parse($php_text);
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

	/**
	 * присваивает каждой ноде id
	 * @param \PHPParser_Node[]| \PHPParser_Node $nodes
	 */
	public static function enumerate($nodes)
	{

	}
} 