<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';

class Core_Flow_VarLimits_Test extends PHPUnit_Framework_TestCase
{
	public function test_base()
	{
		$var = new \Core\Flow\VarLimits();
		$var->addAndBottomBound(10);
		$var->addAndTopBound(15);
		$var->addAndTopBound(25);

		$bounds = $var->getBounds();

		$this->assertEquals(array(array(
			'>' => 10,
			'<' => 15
		)), $bounds);
	}

	public function test_base2()
	{
		$var = new \Core\Flow\VarLimits();
		$var->addAndBottomBound(10);
		$var->addAndTopBound(15);
		$var->addAndTopBound(5);

		$bounds = $var->getBounds();

		$this->assertEquals(array(array(
			'>' => 10,
			'<' => 5
		)), $bounds);
	}

	public function test_or()
	{
		$var = new \Core\Flow\VarLimits();
		$var->addOrBottomBound(10);
		$bounds = $var->getBounds();

		$this->assertEquals(array(array(
			'>' => 10,
		)), $bounds);
	}

	public function test_or2()
	{
		$var = new \Core\Flow\VarLimits();
		$var->addOrBottomBound(10);
		$var->addAndTopBound(12);
		$bounds = $var->getBounds();

		$this->assertEquals(array(array(
			'>' => 10,
			'<' => 12
		)), $bounds);
	}

	public function test_or3()
	{
		$var = new \Core\Flow\VarLimits();
		$var->addAndBottomBound(1);
		$var->addOrTopBound(10);
		$bounds = $var->getBounds();

		$this->assertEquals(array(
			array(
				'>' => 1,
			),
			array(
				'<' => 10,
			)
		), $bounds);
	}

	public function test_eq()
	{
		$var = new \Core\Flow\VarLimits();
		$var->addAndEqual(10);
		$var->addAndEqual(1);
		$bounds = $var->getBounds();

		$this->assertEquals(array(
			array(
				'=' => array(10, 1)
			)
		), $bounds);
	}
}