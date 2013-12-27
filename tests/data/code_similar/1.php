<?php
/**
 *
 * @author k.vagin
 */

$a = date("Y", time());
if ((int)$a % 2 == 0) {
	$result = true;
}
else {
	$result = false;
}

return $result;