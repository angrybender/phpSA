<?php
/**
 * преобразование типов без присваивания
 * @author k.vagin
 */

namespace Checkers;


class TypeConversionWithoutAssets extends \Analisator\ParentChecker
{
	protected $types = array(
		CHECKER_ERRORS
	);

	protected $error_message = 'Ошибочная конструкция "(foo)$bar;" очевидно, забыто присваивание';

	protected $extractor = 'Full'; // класс-извлекатель нужных блоков

	protected $is_line_return = true; // по умолчанию, строка ошибки определяется по началу блока, но функция проверки  может ее переопределить
	protected $line = array();

	private $casts = array(
		'T_ARRAY_CAST',
		'T_BOOL_CAST',
		'T_DOUBLE_CAST',
		'T_INT_CAST',
		'T_OBJECT_CAST',
		'T_STRING_CAST'
	);

	private $scalar_end = array(
		'}',
		'{',
		';'
	);

	private $token_end = array(
		'T_OPEN_TAG',
		'T_COMMENT',
		'T_DOC_COMMENT'
	);

	public function check($tokens)
	{
		foreach ($tokens as $i => $token) {
			if (is_array($token)
				&& in_array($token[0], $this->casts)
				&& ($i>0)
				&& (!is_array($tokens[$i-1]) && in_array($tokens[$i-1], $this->scalar_end)
					|| is_array($tokens[$i-1]) && in_array($tokens[$i-1][0], $this->token_end))
			) {
				$this->line[] = $token[2];
			}
		}

		return empty($this->line);
	}
} 