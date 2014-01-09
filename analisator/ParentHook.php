<?php
/**
 * хуки работают после обработчиков, и не работают с файлами
 * запускаются один раз
 * @author k.vagin
 */

namespace Analisator;


class ParentHook {

	/**
	 * у каждого содержит ссылку на текущий экз
	 * @var
	 */
	public static $instance;

	/**
	 * запуск
	 */
	public function run()
	{}

	public function __construct()
	{
		self::$instance = $this;
	}
}