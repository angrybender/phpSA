<?php

$a = json_decode($json);

foreach ($a as $i => $item) {
	$a[$i] = (array)$item;
}

echo $a[1];