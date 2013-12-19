<?php

// проверка работоспособности системы тестирования

class SmokeTest extends PHPUnit_Framework_TestCase
{
	public function testRun()
	{
		$this->assertEquals(true, true);
	}
}