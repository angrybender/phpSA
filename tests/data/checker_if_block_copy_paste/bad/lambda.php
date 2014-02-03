<?php
/**
 *
 * @author k.vagin
 */

function a($a)
{
	$fn = function($a) {
		if ($a == 1) $b = 2;
		if ($a == 2) $b = 20;
		if ($a == 4) $b = 0;
		if ($a == 7) $b = 16;

		$b++;
		return $b;
	};

	return $fn($a);
}