<?php
/**
 *
 * @author k.vagin
 */

namespace Analisator;


class Report {
	/**
	 * object instance
	 * @var Report
	 */
	protected static $instance = array();

	/**
	 * Защищаем от создания через new Singleton
	 */
	protected function __construct()
	{
		//
	}

	/**
	 * Защищаем от создания через клонирование
	 */
	protected function __clone()
	{
		//
	}

	/**
	 * Защищаем от создания через unserialize
	 */
	protected function __wakeup()
	{
		//
	}

	/**
	 * Создавался ли инстанс
	 */
	public static function isNewInstance()
	{
		$child_class = get_called_class();
		return !isset(self::$instance[$child_class]);
	}

	/**
	 * Возвращает единственный экземпляр класса.
	 * @return \Analisator\Report
	 */
	public static function getInstance()
	{
		$child_class = get_called_class();
		if (self::isNewInstance()) {
			self::$instance[$child_class] = new $child_class;
		}

		return self::$instance[$child_class];
	}


	private $errors = array();
	private $current_file = '';
	private $cur_err_count = 0;

	/**
	 * вставляет ошибку
	 * @param string $message
	 * @param string $checker
	 * @param int|array $line	если массив, то соответствующе оформляется
	 */
	public function addError($message="", $checker="", $line=0)
	{
		$this->errors[] = array(
			'file' => $this->current_file,
			'message' => $message,
			'checker' => $checker,
			'line' => $line
		);

		$this->cur_err_count = $this->cur_err_count + is_array($line) ? count($line) : 1;
	}

	/**
	 * какой сейчас проверяется файл
	 * @param string $file_name
	 */
	public function reportFile($file_name = "")
	{
		$this->current_file = $file_name;
		$this->cur_err_count = 0;
	}

	public function getRawErrors()
	{
		return $this->errors;
	}

	public function getErrCounts()
	{
		return $this->cur_err_count;
	}
} 