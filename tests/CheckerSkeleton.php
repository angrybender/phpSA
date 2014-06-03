<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../bootstrap.php';

class CheckerSkeleton extends PHPUnit_Framework_TestCase
{
	protected $base_path = '';
	protected $class_name = '';
	protected $is_need_token_convert = true;

	protected function run_checker($file_name)
	{
		$code = file_get_contents($file_name);
		if ($this->is_need_token_convert) {
			$code = \Core\Tokenizer::parser($code);
		}

		/** @var \Analisator\ParentChecker $checker */
		$checker_name = "\\Checkers\\{$this->class_name}";
		$checker = new $checker_name($code, $file_name);

		$errors = $checker->get_errors();
		return count($errors) == 0;
	}

	/**
	 * @dataProvider provider_good
	 */
	public function test_good($file_name)
	{
		$result = $this->run_checker($file_name);

		$this->assertEquals(true, $result);
	}

	/**
	 * @dataProvider provider_bag
	 */
	public function test_bad($file_name)
	{
		$result = $this->run_checker($file_name);

		$this->assertEquals(false, $result);
	}

	public function provider_good()
	{
		$files = scandir($this->base_path.'good/');
		$result = array();
		foreach ($files as $file) {
			if ($file !== '.' && $file !== '..') {
				$result[] = array($this->base_path . 'good/' . $file);
			}
		}

		return $result;
	}

	public function provider_bag()
	{
		$files = scandir($this->base_path . 'bad/');
		$result = array();
		foreach ($files as $file) {
			if ($file !== '.' && $file !== '..') {
				$result[] = array($this->base_path . 'bad/' . $file);
			}
		}

		return $result;
	}
} 