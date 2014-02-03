<?php
/**
 *
 * @author k.vagin
 */

function a($a)
{
	if ($a) {
		if ($a == 1) $b = 2;
		if ($a == 2) $b = 20;
		if ($a == 4) $b = 0;
		if ($a == 7) $b = 16;

		$b++;
		return $b > 0;
	}

	return false;
}