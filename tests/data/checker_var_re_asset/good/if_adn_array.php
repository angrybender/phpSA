<?php
/**
 *
 * @author k.vagin
 */

function _makeArray(&$data, $key)
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