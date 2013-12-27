<?php

$year = date('Y', time());

if ((int)$year % 2 == 0)
	$result = true;
else
	$result = false;

return $result;