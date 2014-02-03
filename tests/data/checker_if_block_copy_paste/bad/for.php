<?php
/**
 *
 * @author k.vagin
 */

function a($a)
{
	for ($i = 0; $i++ ; $i<$a) {
		if ($a == 1) $b = 2;
		if ($a == 2) $b = 20;
		if ($a == 4) $b = 0;
		if ($a == 7) $b = 16;

		return $i + $b;
	}

	return $a;
}