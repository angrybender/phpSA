<?php
/**
 * обработчики каждого файла
 * все обработчики вызываются первыми, обработчик инициализируется в начале работы
 * и работает до конца анализа (одни объект)
 * @author k.vagin
 */

namespace Analisator;


class ParentWorker {

	/**
	 * у каждого воркера содержит ссылку на текущий экз
	 * @var
	 */
	public static $instance;

	/**
	 * обработать очередной файл
	 * @param $source_code
	 */
	public function work($source_code)
	{}

	public function __construct()
	{
		self::$instance = $this;
	}
}