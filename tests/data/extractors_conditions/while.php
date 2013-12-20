<?php

$i = 1;
while ($i <= 10) {
	echo $i++;
}

while ($i <= 10):
	$i++;
endwhile;

while ($i<10) echo $i;