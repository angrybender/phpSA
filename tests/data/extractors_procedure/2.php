<?php

$a = 1;
function my_func()
{
	$a = function()
	{
		return time();
	};

	return true;
}

function my_func2($a = 1, $b, array $c)
{
	if (empty($c)) {
		return $a+$b;
	}

	return false;
}

$b = my_func();