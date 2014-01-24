<?php
namespace Checkers;

class ViewAndLogicSpaghetti extends \Analisator\ParentChecker
{
	protected $types = array(
		CHECKER_HEURISTIC
	);

	protected $error_message = 'Возможно смешение логики и представления';

	protected $extractor = 'Procedure'; // класс-извлекатель нужных блоков

	public function check($code, $full_tokens)
	{

	}
}