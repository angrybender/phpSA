<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../CheckerSkeleton.php';

class TypeConversionWithoutAssets_mock extends \Checkers\TypeConversionWithoutAssets {
	public function __construct($source_code)
	{

	}
}

class Checker_TypeConversionWithoutAssets extends CheckerSkeleton
{
	protected $base_path = 'data/checker_type_conversion_without_assets/';
	protected $mock_class_name = 'TypeConversionWithoutAssets_mock';
	protected $is_need_token_convert = true;
} 