<?php
	// $Id: class.Config.php 1994 2013-05-06 15:10:55Z duhon $

	/// Работа с конфигурацией
	class Config
	{
		private static $_includes    = array();
		private static $_files_stack = array();
		private static $_storage = array();
		protected static $_is_alias;
		/**
		 * Текущий алиас, если найден
		 */
		private static $_alias = '';


		/**
		 * Функция парсит xml файл и сохраняет результат выборки в файловый php-кеш.
		 * В дальнейшем этот файл инклудится и результат его работы попадает в global scope
		 *
		 * @param string $file_name
		 * @param bool $caching
		 * @return boll true или false
		 *
		 **/

		public static function includeConfig($file_name, $caching = true)
		{
			$res = true;

			if (!file_exists($file_name)) {
				$res = false;
			} else {

				// Лишнее обращение к файловой системе
				/*if (!is_dir(CACHE_DIR . "config")) {
					mkdir(CACHE_DIR . "config");
				}*/

				// Проверка "девелоперского" конфига, то ест конфига который не добавлен в репозиторий
				// В версионных конфигах в имени присутствуют цифры. Модификатор "s" вообще тут не нужен.
				preg_match("/(.*?)([_A-Za-z.0-9]+$)/", $file_name, $name_matches);
				$dev_config = $name_matches[1] . 'dev_' . $name_matches[2];
				if (file_exists($dev_config)) {
					$file_name = $dev_config;
				}
				// См. ./test/phpunit.bootstrap.php
				if(defined('PHPUNIT_BOOTSTRAP_INC')) {
					$test_config = $name_matches[1] . 'test_' . $name_matches[2];
					if (file_exists($test_config)) {
						$file_name = $test_config;
					}
				}
				$file = CACHE_DIR . "config" . DS;
				$folder = substr(dirname($file_name), (strlen(CONFIG_DIR) - 1));
				if (substr($folder, 0, 1) == DS) {
					$folder = substr($folder, 1);
				}
				if (!empty($folder)) {
					$file .= $folder . DS;
					if (!is_dir($file)) {
						$folder_ar = explode(DS, $folder);
						$prev = DS;
						foreach($folder_ar as $fl) {
							if (!empty($fl)) {
								$prev .= $fl . DS;
								if (!is_dir(CACHE_DIR . "config" . $prev)) {
									mkdir(CACHE_DIR . "config" . $prev);
								}
							}
						}
					}

					if (!is_writable($file)) {
						chmod($file, 0777);
					}
				}
				$file .= basename($file_name, '.xml') . '.php';


				if ($caching) {
					if (!file_exists($file) || (filemtime($file) < filemtime($file_name))) {

						array_push(self::$_files_stack, $file_name);

						$_fake = null;

						$handle = fopen($file, "wt");
						$content = "<?php\n/**\n * Compiled: " . $file_name . " " . date("Y-m-d H:i:s") . "\n */\n\n\n";
						$parsed = self::_parseFile($file_name);
						$var_names = self::_writeConfig($content, $parsed, $_fake, true);
						$folder = str_replace('\\', '/', $folder); // Анти-виндоуз
						$folder = str_replace('/', '::', $folder); // Меняем на внутренний стиль
						foreach ($var_names as $value) {
							$content .= "\nConfig::set('$folder::$value', $$value);\n";
							$content .= "\nunset($$value);\n\n";
						}
						$content .= "\n?" . ">";

						fwrite($handle, $content);

						array_pop(self::$_files_stack);
					}

					require_once($file);

				} else {
					$parsed_config = self::_parseFile($file_name);
					foreach ($parsed_config as $key => $value) {
						self::set($key, $value);
					}
				}
			}

			return $res;
		}

		/**
		 * Функция сохраняет переменные в конфиге
		 *
		 * @param string $key (запись ведётся через точку - api.host и т.д.)
		 * @param mixed $value значение переменной
		 * @return true
		 */

		public static function set($key, $value)
		{
			$var = & self::_getVarByKey($key, true);
			$var = $value;

			return true;
		}

		/**
		 * Получение переменной из конфига
		 *
		 * @param string $key ключ переменной
		 * @param bool if_exists получить ключ, если он существует (тихий вариант)
		 * @return mixed значение переменной
		 */

		public static function & get($key, $if_exists = false)
		{
			return self::_getVarByKey($key, false, $if_exists);
		}

		/**
		* Получение переменной из конфига с приоритетом на корневые конфиги
		* @todo возможно не нужно так как есть dev_config
		* @param mixed $key
		* @param mixed $if_exists
		*/
		public static function altGet($key, $if_exists = false)
		{
			$key_without_folder = preg_replace('/.*:/', '', $key);
			return ($result = self::_getVarByKey($key_without_folder, false, $if_exists)) || ($key_without_folder == $key)
				? $result
				: self::_getVarByKey($key, false, $if_exists);
		}

		/**
		 * Возвращаем информацию о том работаем ли мы на поддомене
		 * @see host.xml таг <alias>
		 * @return boolean
		 */
		public static function isAlias()
		{
			return self::$_is_alias;
		}

		/**
		 * Возвращаем информацию о поддомене-алиасе основного домена, если isAlias()
		 * @see host.xml таг <alias>
		 * @return boolean
		 */
		public static function getAlias()
		{
			return self::isAlias() ? self::$_alias : false;
		}

		/**
		 * Функция возвращает данные из конфига по их ключу либо создаёт новую
		 *
		 * @param string $key ключ данных
		 * @param bool $create создать переменную
		 * @param bool if_exists получить ключ, если он существует (тихий вариант)
		 * @return mixed
		 */

		private static function & _getVarByKey($key, $create = false, $if_exists = false)
		{
			/**
			 * Для подключения конфигов из определенных папок будем обращаться так:
			 *
			 * folder::section.section
			 *
			 */
			$new_key = $folder = null;
			$query = explode('::', $key);
			krsort($query); // Последний элемент будет именем файла, первые — путём к папке
			foreach ($query as $elem) {
				if ($elem) {
					if ($new_key) {
						$folder = $elem . DS . $folder; // Многоуровневая вложенность
					} else {
						$new_key = $elem;
					}
				}
			}
			$key = $new_key;
			// Проверим - подключен ли нужный конфиг
			$_root = explode('.', $key);
			$_root = array_shift($_root);
			if (!isset(self::$_storage[$folder])) {
				self::$_storage[$folder] = array();
			}
			if (!isset(self::$_storage[$folder][$_root])) {
				if ($folder === null) {
					self::includeConfig(CONFIG_DIR . "$_root.xml");
				} else {
					self::includeConfig(CONFIG_DIR . $folder . "$_root.xml");
				}
			}

			// Устанавливаем значение переменной $_is_alias признак работы на поддомене
			// Кривая замена одиночки
			if ($_root == 'host' && !isset(self::$_is_alias) && empty($folder) && !empty(self::$_storage[$folder]['host'])) {
				self::$_is_alias = false;
				if (!empty(self::$_storage[$folder]['host']['alias']) && !empty($_SERVER['HTTP_HOST'])
				&& !empty($_SERVER['REQUEST_URI'])) {
					$request = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
					foreach (self::$_storage[$folder]['host']['alias'] as $val) {
						if (CString::substr($val, '-1') == '/') {
							$val = CString::substr($val, 0, CString::strlen($val)-1);
						}
						if (false !== CString::strpos($request, $val)) {
							self::$_is_alias = true;
							self::$_alias = $val;

							break;
						}
					}
				}
			}

			if (strpos($key, '.') !== false) {
				$parts = explode('.', $key);
				$part = array_shift($parts);
				if (empty(self::$_storage[$folder][$part])) {
					if ($create == true) {
						self::$_storage[$folder][$part] = array();
					} else {
						// TODO: зачем передача по ссылке?
						$res = null;

						return $res;
					}
				}

				$piece = & self::$_storage[$folder][$part];
				foreach ($parts as $part) {
					if (!is_array($piece)) {
						if ($create == true) {
							$piece = array();
						} else {
							if ($_root == 'host' && !$if_exists) {
								trigger_error("invalid_config_parameter_access:$key", E_USER_WARNING);
							}
						}
					}

					$piece = & $piece[$part];
				}

				return $piece;
			}

			if (!isset(self::$_storage[$folder][$key]) && $create == true) {
				self::$_storage[$folder][$key] = array();
			}

			return self::$_storage[$folder][$key];
		}

		private static function _parseFile($file_name)
		{
			$config = array();
			$xml_file = simplexml_load_file($file_name, null, LIBXML_NOCDATA);
			self::_travelNode($xml_file, $config);

			return $config;
		}

		private static function _writeConfig(&$content, &$config, &$keys = null, $root = false)
		{
			$res = null;

			if (is_null($keys)) {
				$keys = array();
				if (isset(self::$_includes[end(self::$_files_stack)])) {
					foreach (self::$_includes[end(self::$_files_stack)] as $file) {
						$content .= __CLASS__ . '::includeConfig(\'' . realpath(dirname(end(self::$_files_stack))).'/'.$file . "');\n";
					}
					$content .= "\n";
				}
			}

			foreach ($config as $key => $value) {
				array_push($keys, (string)$key);
				if (is_array($value)) {

					if ($root === true) {
						$res[] = $keys[0];
					}

					self::_writeConfig($content, $config[$key], $keys);
				} else {
					//if (!in_array($))
					$line  = "$" . $keys[0] . "['";
					$line .= implode('\'][\'', array_slice($keys, 1));
					$line .= '\'] = ';
					$value = addslashes($value);
					if (substr($value, 0, 2) == '::') {
						$line .= '&' . self::_parsePath(substr($value, 2));
					} else {
						$parsed_value = preg_replace('/\${(.*)}/Ue', "'\' . ' . self::_parsePath('\\1') . ' . \''", $value);
						if (preg_match('/\$([a-z]+)\[/', $parsed_value, $matches)) {
							$found = $matches[1];
							if (!empty($found) && !in_array($found, $keys)) {
								$parsed_value = preg_match('/\${(.*)}/Ue', $value, $matches);
								$line .= __CLASS__ . "::get('$matches[1]')";
							} else {
								$line .= '\'' . $parsed_value . '\'';
							}
						} else {
							$line .= '\'' . $parsed_value . '\'';
						}
					}
					$line .= ";\n";
					$content .= $line;
				}
				array_pop($keys);
			}

			return $res;
		}

		private static function _parsePath($path)
		{
			$result = '$';

			if ($path) {
				$paths = explode('.', $path);

				$result .= array_shift($paths);

				if (is_array($paths)) {
					foreach ($paths as $_path) {
						$result .= "['" . $_path . "']";
					}
				}
			}

			return $result;
		}

		private static function _setValue(&$data, $key, $value)
		{
			if ($value == 'on') {
				$data[$key] = true;
			} elseif ($value == 'off') {
				$data[$key] = false;
			} else {
				$data[$key] = (string)$value;
			}

			return true;
		}

		private static function _parseAttributes($node_name, $attrs, &$data)
		{
			if (isset($attrs['value'])) {
				self::_setValue($data, $node_name, $attrs['value']);
			} elseif (isset($attrs['mount'])) {
				$mount = explode('/', (string)$attrs['mount']);
				if ($mount[1]
					&& (!isset(self::$_includes[end(self::$_files_stack)])
					|| !in_array($mount[0], self::$_includes[end(self::$_files_stack)]))) {
					self::$_includes[end(self::$_files_stack)][] = $mount[0];
				}
				self::_setValue($data, $node_name, '::' . $mount[1]);
			} else {
				foreach ($attrs as $ka => $va) {
					self::_setValue($data[$node_name], $ka, $va);
				}
			}
		}

		private static function _makeArray(&$data, $key)
		{
			if (!is_array($data[$key]) || !isset($data[$key][0])) {
				$temp = $data[$key];
				$data[$key] = array(0 => $temp);
				$i = 1;
			} else {
				$i = count($data[$key]);
			}
			return $i;
		}

		private static function _travelNode(&$node, &$data)
		{
			foreach ($node as $key => $value) {
				if (count($value->children()) > 0) {
					$attrs = $value->attributes();
					if (is_array($data) && array_key_exists($key, $data)) {
						$i = self::_makeArray($data, $key);
						self::_travelNode($value, $data[$key][$i]);
					} else {
						self::_travelNode($value, $data[$key]);
					}
					if (count($attrs) > 0) {
						self::_parseAttributes($key, $attrs, $data);
					}
				} elseif (is_array($data) && array_key_exists($key, $data)) {
					$attrs = $value->attributes();
					$i = self::_makeArray($data, $key);
					if (count($attrs) > 0) {
						self::_parseAttributes($i, $attrs, $data[$key]);
					} else {
						$value = (string) $value;
						if (!empty($value)) {
							self::_setValue($data[$key], $i, $value);
						}
					}
				} else {
					$attrs = $value->attributes();
					if (count($attrs) > 0) {
						self::_parseAttributes($key, $attrs, $data);
					} else {
						self::_setValue($data, $key, $value);
					}
				}
			}
		}
	}
