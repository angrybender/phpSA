<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';

use \Core\Flow\Trace;

class Core_Flow_Trace_Cache_Test extends PHPUnit_Framework_TestCase
{
	public function testFunction()
	{
		$return_var = new Trace\Variable();
		$return_var->setObjectName(array('testObject'));

		Trace\CallsResolver::setFunction(array('/'), 'testFunction', array(1,2), $return_var);
		$fetch = Trace\CallsResolver::getFunction(array('/'), 'testFunction');

		$this->assertNotEmpty($fetch);
		$dscr = $fetch[0];

		$this->assertEquals(array('/'), $dscr->getNameSpace());
		$this->assertEquals($return_var, $dscr->getReturn());

		$fetch = Trace\CallsResolver::getFunction(array('/'), 'testFunction2');
		$this->assertEmpty($fetch);
	}

	public function testSetFunctions()
	{
		$return_var = new Trace\Variable();
		$return_var->setObjectName(array('testObject2'));

		Trace\CallsResolver::setFunction(array('/', 'test2', 'ext'), 'testFunction', array(), $return_var);
		Trace\CallsResolver::setFunction(array('/', 'test2'), 'testFunction', array(1,2), $return_var);
		Trace\CallsResolver::setFunction(array('/', 'test2'), 'testFunction', array(3,4), $return_var);

		$fetch = Trace\CallsResolver::getFunction(array('/', 'test2'), 'testFunction');

		$this->assertEquals(2, count($fetch));

		foreach ($fetch as $item) {
			$this->assertEquals($return_var, 			$item->getReturn());
			$this->assertEquals(array('/', 'test2'), 	$item->getNameSpace());
		}
	}

	public function testSimilarFetch()
	{
		$return_var = new Trace\Variable();
		$return_var->setObjectName(array('testObject3'));

		Trace\CallsResolver::setFunction(array('/', 'test3', 'ext'), 'testFunction2', array(null), $return_var);
		Trace\CallsResolver::setFunction(array('/', 'test3', 'ext'), 'testFunction', array(5,7), $return_var);
		Trace\CallsResolver::setFunction(array('/', 'test3', 'ext20'), 'testFunction', array(10, 11), $return_var);

		$fetch = Trace\CallsResolver::getFunction(array('/', 'test3'), 'testFunction', true);

		$this->assertEquals(2, count($fetch));

		foreach ($fetch as $item) {
			$this->assertEquals($return_var, $item->getReturn());
		}

		$fetch = Trace\CallsResolver::getFunction(array('/', 'ext20'), 'testFunction', true);

		$this->assertNotEmpty($fetch);
		$dscr = $fetch[0];

		$this->assertEquals(array('/', 'test3', 'ext20'), $dscr->getNameSpace());
		$this->assertEquals($return_var, $dscr->getReturn());
	}


	public function testClass()
	{
		Trace\CallsResolver::setClass(array('/'), 'testClass1');
		Trace\CallsResolver::setClass(array('/', 'ns1'), 'testClass1');
		$fetch = Trace\CallsResolver::getClass(array('/'), 'testClass1');

		$this->assertNotEmpty($fetch);
		$dscr = $fetch[0];

		$this->assertEquals(array('/'), $dscr->getNameSpace());
		$this->assertEquals('testClass1', $dscr->getObject());
	}

	public function testSetClass()
	{
		Trace\CallsResolver::setClass(array('/', 'ns2'), 'testClass2');
		Trace\CallsResolver::setClass(array('/', 'ns2'), 'testClass2');


		$fetch = Trace\CallsResolver::getClass(array('/', 'ns2'), 'testClass2');

		$this->assertEquals(2, count($fetch));

		foreach ($fetch as $dscr) {
			$this->assertEquals(array('/', 'ns2'), $dscr->getNameSpace());
			$this->assertEquals('testClass2', $dscr->getObject());
		}
	}

	public function testSimilarClass()
	{
		Trace\CallsResolver::setClass(array('/', 'ns2'), 'testClass3');
		Trace\CallsResolver::setClass(array('/', 'ns2'), 'testClass3');


		$fetch = Trace\CallsResolver::getClass(array('ns2'), 'testClass3', true);

		$this->assertEquals(2, count($fetch));

		foreach ($fetch as $dscr) {
			$this->assertEquals('testClass3', $dscr->getObject());
		}
	}

	public function testMethod()
	{
		$return_var = new Trace\Variable();
		$return_var->setObjectName(array('testObject'));

		Trace\CallsResolver::setMethod(array('/', 'method_ns1'), 'testClass4', 'testMethod', array(10, 11), $return_var);
		Trace\CallsResolver::setMethod(array('/', 'method_ns2'), 'testClass4', 'testMethod', array(10, 11), $return_var);

		$fetch = Trace\CallsResolver::getMethod(array('/', 'method_ns2'), 'testClass4', 'testMethod');
		$this->assertNotEmpty($fetch);
		$dscr = $fetch[0];

		$this->assertEquals($return_var, 				$dscr->getReturn());
		$this->assertEquals(array('/', 'method_ns2'), 	$dscr->getNameSpace());

		$fetch = Trace\CallsResolver::getMethod(array('/', 'method_ns2'), 'testClass', 'testMethod');
		$this->assertEmpty($fetch);
	}

	public function testSimilarMethod()
	{
		$return_var = new Trace\Variable();
		$return_var->setObjectName(array('testObject3'));

		Trace\CallsResolver::setMethod(array('/', 'method_ns3'), 'testClass6', 'testMethod', array(10, 11), $return_var);
		Trace\CallsResolver::setMethod(array('/', 'method_ns4'), 'testClass6', 'testMethod', array(10, 11), $return_var);
		Trace\CallsResolver::setMethod(array('/', 'method_ns4'), 'testClass7', 'testMethod', array(12, 13), $return_var);

		$fetch = Trace\CallsResolver::getMethod(array('/'), 'testClass6', 'testMethod', true);
		$this->assertEquals(2, count($fetch));

		foreach ($fetch as $dscr) {
			$this->assertEquals($return_var, $dscr->getReturn());
			$this->assertEquals('testClass6', $dscr->getObject());
		}
	}
}