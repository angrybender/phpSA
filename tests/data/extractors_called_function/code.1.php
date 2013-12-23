<?php
/**
 *
 * @author k.vagin
 */

$d = 'str';

$e = str_replace("s", "", $d);

if ($e !== 'd') {
	return 0;
}

$e = str_replace("x", "f", $e);