<?php
/**
 *
 * @author k.vagin
 */
function _setValue(&$data, $key, $value)
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