<?php

function __test1($a, $b)
{
	if ($a > 10) {
		return $a - $b;
	}
	elseif ($a < 9) {
		return $a + $b;
	}
	elseif ($a == 8) {
		return $a*$b;
	}
	elseif ($a > 7.5) {
		return $a || $b;
	}
	elseif ($a > 7.9) {
		return $a ^ $b;
	}

	return $a/$b;
}
