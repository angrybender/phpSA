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
} 