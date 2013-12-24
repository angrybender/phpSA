<?php
/**
 *
 * @author k.vagin
 */
function _get_mod_time($dir)
{
	// filemtime() will return false, but it does raise an error.
	$date = (@filemtime($dir)) ? filemtime($dir) : getdate($this->now);

	$time['file_mtime'] = ($date['hours'] << 11) + ($date['minutes'] << 5) + $date['seconds'] / 2;
	$time['file_mdate'] = (($date['year'] - 1980) << 9) + ($date['mon'] << 5) + $date['mday'];

	return $time;
}
