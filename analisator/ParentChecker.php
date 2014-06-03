<?php
/**
 *
 * @author k.vagin
 */

namespace Analisator;


abstract class ParentChecker {

	/**
	 * тип чекера из def.types_of_checkers.php
	 * @var array
	 */
	protected $types = array(
		CHECKER_OTHER
	);

	protected $error_message = ''; // текст ворнинга

	protected $errors_line = array();

	/**
	 * @param $nodes
	 */
	abstract protected function check($nodes);

	/**
	 * полный путь к текущему обрабатываемому файлу
	 * @var string
	 */
	protected $file = '';

	protected function set_error($line)
	{
		/*Report::getInstance()->addError(
			$this->error_message,
			get_class($this),
			$line
		);*/
		$this->errors_line[] = $line;
	}

	public function get_errors()
	{
		return array_unique($this->errors_line);
	}

	/**
	 * @param array $nodes
	 * @param string $source_file_path
	 */
	public function __construct($nodes, $source_file_path = '')
	{
		$this->file = $source_file_path;
		$this->check($nodes);
	}
} 