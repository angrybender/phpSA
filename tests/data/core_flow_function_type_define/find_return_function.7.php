<?php

function __test1($a, $b)
{
	$empty = false;

	if ($a > $b) {
		$result = new \stdClass();

		$result->left = $a;
		$result->right = $b;
	}
	else {
		$result = $empty;
	}

	return $result;
}
