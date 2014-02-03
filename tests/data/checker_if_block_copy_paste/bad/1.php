<?php
/**
 *
 * @author k.vagin
 */

function a($a)
{
	if ($a == 1) $b = 2;
	if ($a == 2) $b = 20;
	if ($a == 4) $b = 0;
	if ($a == 7) $b = 16;

	$b++;

	if ($b == 1) $c = $a+$b;
	if ($b == 2) $c = $a+$b;

	$c++;

	if ($c == 4) $b = 0;
	if ($c == 7) $b = 16;
	if ($c == 7) $b = 16;

	return $c-1;
}