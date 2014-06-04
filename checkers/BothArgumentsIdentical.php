<?php
namespace Checkers;

/**
 * ловит ошибки вроде $a && $a
 * Class BothArgumentsIdentical
 * @package Checkers
 */
class BothArgumentsIdentical extends \Analisator\ParentChecker
{
	protected $types = array(
		CHECKER_ERRORS
	);

	protected $error_message = 'Идентичные операнды';

	public function check($nodes)
	{

	}
}