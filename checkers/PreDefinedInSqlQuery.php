<?php
/**
 *
 * @author k.vagin
 */

namespace Checkers;


class PreDefinedInSqlQuery extends \Analisator\ParentChecker
{
	protected $types = array(
		CHECKER_HEURISTIC,
		CHECKER_ERRORS
	);

	protected $error_message = 'В строку, которая скорее всего является sql запросом, без экранирования подставляется значение из GET/POST запроса';

	protected $extractor = 'Full';

	protected $is_line_return = true;
	protected $line = array();

	protected $request_vars = array(
		'$_GET',
		'$_POST',
		'$_REQUEST'
	);

	private $tokens;

	public function check($tokens)
	{
		foreach ($tokens as $i => $token) {
			if (is_array($token) && ($token[0] === 'T_COMMENT' || $token[0] === 'T_DOC_COMMENT')) {
				unset($tokens[$i]);
			}
		}
		$tokens = array_values($tokens);
		$this->tokens = $tokens;

		foreach ($tokens as $i => $token) {
			if (is_array($token)
				&& $token[0] === 'T_VARIABLE'
				&& in_array(strtoupper($token[1]), $this->request_vars)
			) {
				$this->process($i, $token[2]);
			}
		}

		return empty($this->line);
	}

	/**
	 * ищет вокруг данной позиции
	 * @param $position
	 * @param $line
	 */
	private function process($position, $line)
	{
		$is_curly_open = $this->process_curly_open($position, $line);

		if (!$is_curly_open) {
			$this->process_concatenation($position, $line);
		}
	}

	/**
	 * обрабатывает строки вида "{$a}"
	 * @param $position
	 * @param $line
	 * @return bool
	 */
	private function process_curly_open($position, $line)
	{
		$is = (is_array($this->tokens[$position-1]) && $this->tokens[$position-1][0] === 'T_CURLY_OPEN');
		if (!$is) {
			return false;
		}

		$open_quote = '';
		$start = 0;
		$end = 0;
		for ($j=$position-1; $j>0; $j--) {
			if ($this->tokens[$j] === "\"" || $this->tokens[$j] === '\'') {
				$open_quote = $this->tokens[$j];
				$start = $j+1;
				break;
			}
		}

		if (!$is) { // не нашли начало строки
			return false;
		}

		$is = false;
		for ($j=$position+1; $j < count($this->tokens); $j++) {
			if ($this->tokens[$j] === $open_quote) {
				$is = true;
				$end = $j-1;
				break;
			}
		}

		if (!$is) { // не нашли конец строки
			return false;
		}

		$string = \Tokenizer::tokens_to_source(array_slice($this->tokens, $start, $end-$start));
		$this->process_string(preg_replace('/{(.+)}/', '', $string), $line);

		return true;
	}

	/**
	 * брабатывает строки вида "SELECT * FROM `table` where id = ". $_GET[$field]
	 * @param $position
	 * @param $line
	 * @return bool
	 */
	private function process_concatenation($position, $line)
	{
		$is = ($this->tokens[$position-1] === '.');
		if (!$is || $position < 2) {
			return false;
		}

		if (is_array($this->tokens[$position-2]) && $this->tokens[$position-2][0] === 'T_CONSTANT_ENCAPSED_STRING') {
			$string = substr($this->tokens[$position-2][1], 1, -1); // обрамляюще кавычки удаляем
		}
		else {
			return false;
		}

		if (isset($this->tokens[$position+1]) && $this->tokens[$position+1] == '.') {
			if (isset($this->tokens[$position+2]) && is_array($this->tokens[$position+2]) && $this->tokens[$position-2][0] === 'T_CONSTANT_ENCAPSED_STRING') {
				$string = $string . substr($this->tokens[$position+2][1], 1, -1);
			}
		}

		$this->process_string($string, $line);

		return true;
	}

	/**
	 * анализирует найденную строку, в которую инжектируется переменная без экранирования
	 * @param $string
	 * @param $line
	 * @return bool
	 */
	private function process_string($string, $line)
	{
		if (!\Heuristic::is_maybe_sql_query($string)) {
			return true;
		}

		$this->line[] = $line;
	}
} 