<?php
/**
 *
 * @author k.vagin
 */

namespace Analisator;


abstract class ParentExtractor {

	public $tokens = array();
	public $code = false;

	/**
	 * @param string|array $code
	 */
	public function __construct($code)
	{
		if (!is_array($code)) {

			$is_remove_open_tag = false;
			if (!is_array($code)) {
				if (!\Tokenizer::is_open_tag($code)) {
					$is_remove_open_tag = true;
					$code = '<?php' . $code;
				}
			}

			$result = \Tokenizer::get_tokens($code);
			if ($is_remove_open_tag) {
				unset($result[0]);
				$result = array_values($result);
			}

			$this->tokens = $result;
			$this->code = $code;
		}
		else {
			$this->tokens = $code;
		}
	}

	public function extract(array $filter)
	{}
} 