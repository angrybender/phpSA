<?php
/**
 *
 * @author k.vagin
 */

$d = 'str';

$e = str_replace("s", "", $d);
$e = str_replace("g", "", $d);
$e = str_replace("g", "", $d);

if ($e !== 'd') {
	return 0;
}

$e = str_replace("x", "f", $e);