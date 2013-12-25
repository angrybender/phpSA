<?php
/**
 *
 * @author k.vagin
 */

function mf($cmd, $regexp)
{
	$mime = @exec($cmd, $mime, $return_status);
	if ($return_status === 0 && is_string($mime) && preg_match($regexp, $mime, $matches))
	{
		$this->file_type = $matches[1];
		return;
	}
}