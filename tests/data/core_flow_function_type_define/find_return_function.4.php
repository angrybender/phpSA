<?php

function __test1($a, $b)
{
	$a = array_map(function($item) {
		return $item % 2;
	}, $a);

	$b = array_map(function($item) {
		return $item % 2;
	}, $b);

	return array_merge($a, $b);
}
