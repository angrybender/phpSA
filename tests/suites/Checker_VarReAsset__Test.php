<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../CheckerSkeleton.php';

class VarReAsset_mock extends \Checkers\VarReAsset {
	public function __construct($source_code)
	{

	}
}

class Checker_VarReAsset extends CheckerSkeleton
{
	protected $base_path = 'data/checker_var_re_asset/';
	protected $mock_class_name = 'VarReAsset_mock';
	protected $is_need_token_convert = true;
} 