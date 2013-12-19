<?php

class MyPHPUnit_Util_Printer extends PHPUnit_Util_Printer
{
	/**
	 * @param  string $buffer
	 */
	public function write($buffer)
	{

	}
}

class CustomTestListener extends MyPHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{
	private $is_error = false;
	private $errors = array();
	private $count = 0; // всего протестировано наборов данных
	private $suites = 0; // всего тестов
	private $clear_time = 0; // чистое время тестов
	private $script_start = 0;

	private function _lock()
	{
		file_put_contents(__DIR__ . '/lock.lock', '1');
	}

	private function _unlock()
	{
		@unlink(__DIR__ . '/lock.lock');
	}

	private function read_stat()
	{
		if (file_exists(__DIR__ . '/results.bin')) {
			$stat = unserialize(file_get_contents(__DIR__ . '/results.bin'));
			$this->count = $stat['count'];
			$this->suites = $stat['suites'];
			$this->clear_time = $stat['clear_time'];
		}
	}

	private function write_stat()
	{
		$stat = array(
			'count' => $this->count,
			'suites' => $this->suites,
			'clear_time' => $this->clear_time
		);

		file_put_contents(__DIR__ . '/results.bin', serialize($stat));
	}

	public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
	{
		$this->is_error = true;
		echo "\033[31m", 'E', "\033[0m";
	}

	public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
	{
		$this->is_error = true;
		$this->errors[] = $test->getName() . ' / ' . "\033[33m" . $e->toString() . "\033[0m";

		echo "\033[31m", 'F', "\033[0m";
	}

	public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
	{
		echo 'I';
	}

	public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
	{
		echo 'S';
	}

	public function startTest(PHPUnit_Framework_Test $test)
	{

	}

	public function endTest(PHPUnit_Framework_Test $test, $time)
	{
		if (!$this->is_error) {
			echo "\033[32m", '.', "\033[0m";
		}
		else {
			$this->_lock();
		}

		$this->is_error = false;
		$this->count++;
	}

	public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
	{
		$this->script_start = microtime(true);

		$this->read_stat();
		$this->_unlock();
	}

	public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
	{
		$this->suites++;
		$this->clear_time = $this->clear_time + (microtime(true) - $this->script_start);

		$this->write_stat();

		if (count($this->errors)>0) {
			echo PHP_EOL;
		}

		/*foreach ($this->errors as $error) {
			echo $error, PHP_EOL;
		}*/

		if (count($this->errors)>0) {
			die();
		}


		$this->errors = array();
	}
}
?>