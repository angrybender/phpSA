<?php

function __test1($a)
{
	if ($a > 10) {
		return false;
	}
	elseif ($a < 9) {
		return "none";
	}
	elseif ($a == 8) {
		return 1.5;
	}

	return 0;
}
