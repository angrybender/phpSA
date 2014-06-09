<?php
namespace Core\Flow;

/**
 * вычисленные ограничения переменной
 * @author k.vagin
 */


class VarLimits
{
	protected $limits = array();

	/**
	 * > | >=
	 * @param $value
	 * @param bool $is_strict
	 */
	public function addOrBottomBound($value, $is_strict = true)
	{
		if ($is_strict) {
			$this->limits[] = array('>' => $value);
		}
		else {
			$this->limits[] = array('>=' => $value);
		}
	}

	/**
	 * > | >=
	 * @param $value
	 * @param bool $is_strict
	 */
	public function addAndBottomBound($value, $is_strict = true)
	{
		if ($is_strict) {
			$key = '>';
		}
		else {
			$key = '>=';
		}

		$this->add_limit($key, $value);
	}

	/**
	 * < | <=
	 * @param $value
	 * @param bool $is_strict
	 */
	public function addOrTopBound($value, $is_strict = true)
	{
		if ($is_strict) {
			$this->limits[] = array('<' => $value);
		}
		else {
			$this->limits[] = array('<=' => $value);
		}
	}

	/**
	 * < | <=
	 * @param $value
	 * @param bool $is_strict
	 */
	public function addAndTopBound($value, $is_strict = true)
	{
		if ($is_strict) {
			$key = '<';
		}
		else {
			$key = '<=';
		}

		$this->add_limit($key, $value);
	}

	public function addOrEqual($value)
	{
		$this->limits[] = array('=' => $value);
	}

	public function addAndEqual($value)
	{
		$this->add_limit('=', $value);
	}

	public function addOrNotEqual($value)
	{
		$this->limits[] = array('!' => $value);
	}

	public function addAndNotEqual($value)
	{
		$this->add_limit('!', $value);
	}

	public function getBounds()
	{
		return $this->limits;
	}

	/**
	 * сворачивает ограничения, например > 10 && >20 будет >20, >10 && >=10 будет >10
	 * @param $type
	 * @param $value
	 */
	protected function add_limit($type, $value)
	{
		if (empty($this->limits)) {
			$this->limits[] = array($type => $value);
			return;
		}

		$last = count($this->limits) - 1;

		if ($type == '=' || $type == '!') {
			if (isset($this->limits[$last][$type]) && !is_array($this->limits[$last][$type])) {
				$this->limits[$last][$type] = array($this->limits[$last][$type]);
			}

			if (is_array($this->limits[$last][$type])) {
				$this->limits[$last][$type][] = $value;
			}
			else {
				$this->limits[$last][$type] = $value;
			}

			return;
		}

		$alias = $type;
		$is_strict = true;
		if ($type == '>' || $type == '<') {
			$alias = $type . '=';
		}

		if ($type == '>=' || $type == '<=') {
			$is_strict = false;
			$alias = substr($type, 0, 1);
		}

		$func = 'min';
		if ($type == '>' || $type == '>=') {
			$func = 'max';
		}

		$is_found = false;
		foreach ($this->limits[$last] as $_key => $condition) {
			$key = '';
			if ($_key == $type) {
				$key = $type;
				$is_found = true;
			}
			elseif ($_key == $alias) {
				$key = $alias;
				$is_found = true;
			}

			if (!$is_found) {
				continue;
			}

			$bound_value = call_user_func($func, $value, $condition);
			if ($bound_value === $value && $is_strict) {
				$this->limits[$last][$_key] = $bound_value;
			}
		}

		if (!$is_found) {
			$this->limits[$last][$type] = $value;
		}
	}
} 