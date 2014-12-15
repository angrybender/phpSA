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

		Trace\Cache::setFunction(array('/'), 'testFunction', array(1,2), $return_var);
		$fetch = Trace\Cache::getFunction(array('/'), 'testFunction');

		$this->assertEquals(array(
			'params'	=> array(1,2),
			'return'	=> $return_var
		), $fetch[0]);
	}

	public function testSetFunctions()
	{
		$return_var = new Trace\Variable();

		Trace\Cache::setFunction(array('/', 'test2', 'ext'), 'testFunction', array(), $return_var);
		Trace\Cache::setFunction(array('/', 'test2'), 'testFunction', array(1,2), $return_var);
		Trace\Cache::setFunction(array('/', 'test2'), 'testFunction', array(3,4), $return_var);

		$fetch = Trace\Cache::getFunction(array('/', 'test2'), 'testFunction');

		$this->assertEquals(array(
			'params'	=> array(1,2),
			'return'	=> $return_var
		), $fetch[0]);

		$this->assertEquals(array(
			'params'	=> array(3,4),
			'return'	=> $return_var
		), $fetch[1]);
	}

	public function testSetFunctionAndArgFilter()
	{
		$return_var = new Trace\Variable();

		Trace\Cache::setFunction(array('/'), 'testFunction3', array(5,7), $return_var);
		Trace\Cache::setFunction(array('/'), 'testFunction3', array(5), $return_var);

		$fetch = Trace\Cache::getFunction(array('/'), 'testFunction3', 1);

		$this->assertEquals(array(
			'params'	=> array(5),
			'return'	=> $return_var
		), $fetch[0]);
	}

	public function testSimilarFetch()
	{
		$return_var = new Trace\Variable();

		Trace\Cache::setFunction(array('/', 'test3', 'ext'), 'testFunction2', array(null), $return_var);
		Trace\Cache::setFunction(array('/', 'test3', 'ext'), 'testFunction', array(5,7), $return_var);
		Trace\Cache::setFunction(array('/', 'test3', 'ext2'), 'testFunction', array(10, 11), $return_var);

		$fetch = Trace\Cache::getFunction(array('/', 'test3'), 'testFunction', null, true);

		$this->assertEquals(array(
			'params'	=> array(5,7),
			'return'	=> $return_var
		), $fetch[0]);

		$this->assertEquals(array(
			'params'	=> array(10,11),
			'return'	=> $return_var
		), $fetch[1]);
	}

	public function testSimilarFetchAndArgFilter()
	{
		$return_var = new Trace\Variable();

		Trace\Cache::setFunction(array('/', 'test4', 'ext'), 'testFunction', array(null), $return_var);
		Trace\Cache::setFunction(array('/', 'test4', 'ext'), 'testFunction', array(7,9), $return_var);
		Trace\Cache::setFunction(array('/', 'test4', 'ext2'), 'testFunction', array(10, 11), $return_var);

		$fetch = Trace\Cache::getFunction(array('/', 'test4'), 'testFunction', 2, true);

		$this->assertEquals(array(
			'params'	=> array(7, 9),
			'return'	=> $return_var
		), $fetch[0]);

		$this->assertEquals(array(
			'params'	=> array(10,11),
			'return'	=> $return_var
		), $fetch[1]);
	}
}