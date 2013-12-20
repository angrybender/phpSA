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
	protected $extractor = ''; // класс-извлекатель нужных блоков

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
			$result = $this->check($block);

			if ($result === false) {
				$reporter->addError(
					$this->error_message,
					get_class($this),
					$block['line']
				);
			}
		}
	}

	protected function extract($source_code)
	{
		if (empty($this->extractor)) {
			throw new \Exception("класс-извлекатель нужных блоков empty"); // todo Exception
		}

		$extractor_obj = new $this->extractor($source_code);
		if (!($extractor_obj instanceof \Analisator\ParentExtractor)) {
			throw new \Exception("класс-извлекатель не \\Analisator\\ParentExtractor"); // todo Exception
		}

		$this->blocks = $extractor_obj->extract();
		if (!$this->is_extract_mandatory) {
			$this->blocks = $source_code;
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