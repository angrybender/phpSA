<?php
/**
 * информация о языке
 * @author k.vagin
 */

class Repository
{
	/**
	 * переменные, которые имеют значение сразу по умолчанию
	 * @var array
	 */
	public static $predefined_vars = array(
		'$_POST',
		'$_SERVER',
		'$_GET',
		'$_POST',
		'$_FILES',
		'$_REQUEST',
		'$_SESSION',
		'$_ENV',
		'$_COOKIE',
		'$GLOBALS',
		'$php_errormsg',
		'$HTTP_RAW_POST_DATA',
		'$http_response_header',
		'$argc',
		'$argv',
		'$this'
	);

	// такие функции, которые некоторый результат пишут в переданную им переменную
	public static $function_callback_into_variable = array(
		'exec' => array(2,3), // возвращает значение в 2 и 3й аргументы
		'preg_match' => array(3),
		'preg_match_all' => array(3),
		'fsockopen' => array(3,4),
		'xml_parse_into_struct' => array(3,4),
		'sqlite_open' => array(3),
		'sqlite_popen' => array(3),
		'preg_replace' => array(5),
		'openssl_sign' => array(2,3),
		'pcntl_waitpid' => array(2),
		'stream_socket_server' => array(2,3),
		'stream_socket_client' => array(2,3),
		'socket_getsockname' => array(2,3),
		'pcntl_wait' => array(1),
		'parse_str' => array(2),
		'getimagesize' => array(2),
		'headers_sent' => array(1,2),
	);

	// такие функции, которые результат пишут в переданную им переменную, но принимают бесконечное кол-во аргументов по ссылке
	public static $function_callback_into_variable_infinity = array(
		'sscanf' => 3, // с третьего аргумента и дальше
	);
}