<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';

class Config_skip_all_mock extends \Analisator\Config {
	protected $config_path = '/../tests/data/config/config_skip_all.ini';
}

class Config_skip_none_mock extends \Analisator\Config {
	protected $config_path = '/../tests/data/config/config_skip_none.ini';
}


class Config extends PHPUnit_Framework_TestCase
{
	public function testSkipAll()
	{
		$obj = Config_skip_all_mock::getInstance();
		$obj->load();

		$this->assertEquals(true, $obj->is_checker_enable('Checkers\TypeConversionWithoutAssets'));
		$this->assertEquals(false, $obj->is_checker_enable('Checkers\FooChecker'));
		$this->assertEquals(false, $obj->is_checker_enable('Checkers\BarChecker'));
	}

	public function testSkipNone()
	{
		$obj = Config_skip_none_mock::getInstance();
		$obj->load();

		$this->assertEquals(true, $obj->is_checker_enable('Checkers\TypeConversionWithoutAssets'));
		$this->assertEquals(true, $obj->is_checker_enable('Checkers\FooChecker'));
		$this->assertEquals(false, $obj->is_checker_enable('Checkers\BarChecker'));
	}
} 