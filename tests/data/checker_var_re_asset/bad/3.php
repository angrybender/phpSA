<?php
/**
 *
 * @author k.vagin
 */

function vb($a, $b)
{
	$b = null;
	unset($b);
	$b = $a;

	return $a + $b;
}