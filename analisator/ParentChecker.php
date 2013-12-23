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

	protected $blocks = array(); // извлеченные для анализа блоки

	/**
	 * флаг - пропускать проверку, если извлекатель ничего не вернул или нет
	 * по умолчанию - пропускать
	 * @var bool
	 */
	protected $is_extract_mandatory = true;

	/**
	 * @var \Analisator\ParentExtractor
	 */
	protected $extractor = ''; // класс-извлекатель нужных блоков, имя без неймспейса

	protected $filter = array(); //фильтр для извлекателя (может не применяться)

	protected $is_line_return = false; // по умолчанию, строка ошибки определяется по началу блока, но функция проверки  может ее переопределить
	protected $line;					// может быть массивом

	/**
	 * public for unit tests
	 * @param $block
	 */
	public function check($block)
	{}

	/**
	 * для каждого блока вызывает ф-ию проверки
	 */
	protected function iteration_check()
	{
		$reporter = Report::getInstance();
		foreach ($this->blocks as $block) {
			$result = $this->check($block['body']);

			$line = $this->is_line_return ? $this->line : $block['line'];

			if ($result === false) {
				$reporter->addError(
					$this->error_message,
					get_class($this),
					$line
				);
			}
		}
	}

	protected function extract($source_code)
	{
		if (empty($this->extractor)) {
			throw new \Exception("класс-извлекатель нужных блоков empty"); // todo Exception
		}

		$this->extractor = 'Extractors\\' . $this->extractor;
		$extractor_obj = new $this->extractor($source_code);
		if (!($extractor_obj instanceof \Analisator\ParentExtractor)) {
			throw new \Exception("класс-извлекатель не \\Analisator\\ParentExtractor"); // todo Exception
		}

		$this->blocks = $extractor_obj->extract($this->filter);

		if (empty($this->blocks) && !$this->is_extract_mandatory) {
			$this->blocks = $source_code; // fixme
		}
	}

	/**
	 * @param array|string $source_code
	 */
	public function __construct($source_code)
	{
		$this->extract($source_code);
		$this->iteration_check();
	}
} 