<?php
/**
 *
 * @author k.vagin
 */

namespace Analisator;


class Suite {

	private $project_path = '';
	private $project_files = array();
	private $checkers = array(); // массивы чекеров
	private $workers = array(); // массив объектов воркеров
	private $hooks = array(); // массив объектов хуков

	/**
	 * @var Report
	 */
	private $reporter = null; // ссылка на объект

	/**
	 * @var Config
	 */
	private $config = null; // ссылка на объект


	private $_tick_cnt = 0;

	// todo вынести работу с файлами в отд класс

	private function include_php_in_path($path)
	{
		$files = @scandir($path);

		if (!is_array($files) || empty($files)) return 0;

		foreach ($files as $file)
		{
			if ($file === '.' || $file === '..') {
				continue;
			}

			if (is_file($path . $file)) {
				include_once $path . $file;
			}
		}
	}

	/**
	 * подключает всё что необходимо
	 */
	private function include_all()
	{
		// todo in config
		$paths = array(
			'checkers/',
			'workers/',
			'hooks/',
		);

		foreach ($paths as $path) {
			$this->include_php_in_path($path);
		}
	}

	/**
	 * обработка конфига
	 */
	private function load_config()
	{
		$this->config = Config::getInstance();
		$this->config->load();
	}

	public function __construct()
	{
		$this->load_config();

		$this->include_all();

		$this->reporter = Report::getInstance();
	}

	public function set_project_path($path)
	{
		if (is_dir($path)) {
			$this->project_path = $path;
		}
		else {
			throw new \Exception("некорректный путь к проекту"); // todo Exception
		}
	}

	/**
	 * определяет подходит ли файл для анализа
	 * todo дописать эвристику
	 *
	 * @param $file_path
	 * @return bool
	 */
	private function is_file_php($file_path)
	{
		$path_info = pathinfo($file_path);
		return (isset($path_info['extension']) && $path_info['extension'] === 'php');
	}

	/**
	 * рекурсивно собирает все файлы из папки
	 * @param $path
	 * @return int
	 */
	private function collect_project_files($path)
	{
		$files = @scandir($path);
		if (!is_array($files)) return 0;

		foreach ($files as $file)
		{
			if ($file === '.' || $file === '..') {
				continue;
			}

			if (is_file($path . $file) && $this->is_file_php($path . $file)) {
				$this->project_files[] = array(
					'path' => $path . $file
				);
			}
			elseif (is_dir($path . $file)) {
				$this->collect_project_files($path . $file . '/');
			}
		}
	}

	/**
	 * кэширует в проперти все доступные чекеры
	 * @throws \Exception
	 */
	private function collect_checkers()
	{
		$classes = get_declared_classes();
		foreach ($classes as $class) {
			if (is_subclass_of($class, "Analisator\\ParentChecker") && $this->config->is_checker_enable($class)) {
				$this->checkers[] = $class;
			}
		}

		if (empty($this->checkers)) {
			throw new \Exception("missing checkers"); // todo Exception
		}
	}

	/**
	 * кэширует в проперти все доступные воркеры
	 */
	private function collect_workers()
	{
		$classes = get_declared_classes();
		foreach ($classes as $class) {
			if (is_subclass_of($class, "Analisator\\ParentWorker")) {
				$this->workers[] = new $class;
			}
		}
	}

	/**
	 * кэширует в проперти все доступные хуки
	 */
	private function collect_hooks()
	{
		$classes = get_declared_classes();
		foreach ($classes as $class) {
			if (is_subclass_of($class, "Analisator\\ParentHook")) {
				$this->hooks[] = new $class;
			}
		}
	}

	/**
	 * анализ файла
	 * @param $file_path
	 */
	protected function run($file_path)
	{
		//file_put_contents('log.txt', $file_path.PHP_EOL, FILE_APPEND);

		$code = file_get_contents($file_path);
		$tokens = \Tokenizer::get_tokens($code);

		try {
			foreach ($this->checkers as $checker) {
				$checker_object = new $checker($tokens, $file_path);
				unset($checker_object);
			}

			$this->print_result();
		}
		catch (\PHPParser_Error $e) {
			// PHPParser очень нестабильно ищет ошибки в пхп коде, когда он смешен с хтмл
			$this->print_result();
		}
		catch (\Exception $e) {
			echo PHP_EOL, $file_path, PHP_EOL;  // todo
			echo $e->getMessage(), PHP_EOL; // todo
			$this->print_result(true);
		}
	}

	/**
	 * действия, которые надо выполнить над каждым файлом перед стартом
	 * @param $file_path
	 */
	protected function pre_run($file_path)
	{
		//file_put_contents('log.txt', $file_path.PHP_EOL, FILE_APPEND);

		$code = file_get_contents($file_path);

		try {
			foreach ($this->workers as $worker) {
				$worker->work($code);
			}
		}
		catch (\Exception $e) {
			die('Worker error: ' . $e->getMessage(). ", file: " . $file_path); // todo maybe exception
		}
	}

	/**
	 * запуск хуков (после воркеров, перед чекерами)
	 */
	protected function run_hooks()
	{
		foreach ($this->hooks as $hook) {
			$hook->run();
		}
	}

	/**
	 * отображает тик проверки (аналогично пхпюнит)
	 * todo в отд. класс
	 * @param bool
	 */
	private function print_result($is_error = false)
	{
		$error_count = $this->reporter->getErrCounts();
		$this->_tick_cnt++;
		if ($this->_tick_cnt === 100) {
			echo PHP_EOL;
			$this->_tick_cnt = 1;
		}

		if ($is_error) {
			echo "\033[31m" . "F" . "\033[0m";
			return;
		}

		if ($error_count === 0) {
			echo "\033[32m", '.', "\033[0m";
		}
		elseif ($error_count <= 5) {
			echo "\033[33m" . "1" . "\033[0m";
		}
		elseif ($error_count <= 10) {
			echo "\033[33m" . "2" . "\033[0m";
		}
		elseif ($error_count <= 50) {
			echo "\033[33m" . "3" . "\033[0m";
		}
		elseif ($error_count <= 100) {
			echo "\033[31m" . "4" . "\033[0m";
		}
		else {
			echo "\033[31m" . "5" . "\033[0m";
		}
	}


	/**
	 * по всем файлам поехали
	 */
	protected function project_files_iterator()
	{
		// воркеры и тд
		echo "Start workers...", PHP_EOL;
		foreach ($this->project_files as $file) {
			$this->pre_run($file['path']);
		}

		// хуки
		echo "Start hooks...", PHP_EOL;
		$this->run_hooks();

		error_reporting(E_ERROR);

		// анализаторы
		foreach ($this->project_files as $file) {
			$this->reporter->reportFile($file['path']);
			$this->run($file['path']);
		}

		error_reporting(E_ALL);

		echo PHP_EOL, PHP_EOL;
	}

	public function start()
	{
		$this->collect_project_files($this->project_path);

		$this->collect_checkers();
		$this->collect_workers();
		$this->collect_hooks();

		$this->project_files_iterator();

		print_r($this->reporter->getRawErrors());
	}

} 