<?php
namespace Checkers;

class Spagetti extends \Analisator\ParentChecker
{
	protected $types = array(
		CHECKER_ERRORS
	);

	protected $error_message = '';

	protected $extractor = ''; // класс-извлекатель нужных блоков

	public function check($code, $full_tokens)
	{

	}
}