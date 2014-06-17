<?php
/**
 *
 * @author k.vagin
 */

namespace Analisator;


class Config {
	/**
	 * object instance
	 * @var Config
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
	 * @return \Analisator\Config
	 */
	public static function getInstance()
	{
		$child_class = get_called_class();
		if (self::isNewInstance()) {
			self::$instance[$child_class] = new $child_class;
		}

		return self::$instance[$child_class];
	}

	public static $cache_path = 'cache';

	protected $config_path = '/../config.ini';

	private $config;
	public $is_all_checkers_disabled = false;
	public $status_checkers_by_class_name = array();
	public $syntax_error = array();

	public function load()
	{
		if (!is_file(__DIR__ . $this->config_path)) {
			return true;
		}

		$configs = parse_ini_file(__DIR__ . $this->config_path, true);

		if (!isset($configs['skipped_checkers_by_class_name'])) {
			throw new \Exception("секция skipped_checkers_by_class_name в конфиге обязательна"); // todo Exception
		}

		// выносим в корень как пропускаем чекеры: разрешаем некоторые, или запрещаем некоторые
		if (isset($configs['skipped_politics']) && isset($configs['skipped_politics']['all'])) {
			$this->is_all_checkers_disabled = ($configs['skipped_politics']['all'] == 1);
			unset($configs['skipped_politics']['all']);
		}
		$configs['is_all_disabled'] = $this->is_all_checkers_disabled;

		// преобразауем для удобства - пишем в отд. ключ или запрещенные чекеры, или разрешенные, в зависимости от $is_all_disabled
		$_checkers = array();
		foreach ($configs['skipped_checkers_by_class_name'] as $checker_name => $status) {
			if ($this->is_all_checkers_disabled && $status == 1) {
				$_checkers[] = 'Checkers\\' . $checker_name;
			}
			elseif (!$this->is_all_checkers_disabled && $status != 1) {
				$_checkers[] = 'Checkers\\' . $checker_name;
			}
		}
		$this->status_checkers_by_class_name = $_checkers;

		// политика проверка синтаксиса
		if (!isset($configs['syntax_error'])) {
			$configs['syntax_error'] = array(
				'print' => true
			);
		}

		$this->syntax_error = $configs['syntax_error'];

		$this->config = $configs;
	}

	/**
	 * @param $class_name передавать __CLASS__
	 * @return bool
	 */
	public function is_checker_enable($class_name)
	{
		if ($this->is_all_checkers_disabled) {
			return in_array($class_name, $this->status_checkers_by_class_name);
		}
		else {
			return !in_array($class_name, $this->status_checkers_by_class_name);
		}
	}
} 