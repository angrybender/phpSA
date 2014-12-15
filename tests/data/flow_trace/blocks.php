<?php
/**
 *
 * @author k.vagin
 */

$a = 1;
$b = false;

if ($a > 0) {
	$t = 2;
}
elseif ($a < -1) {
	$t = 1;
}
else {
	$t = 0;
}

for ($i = 0, $j = 1; $i < 20; $i++ ) {
	echo $i + 1;
	print_r($j - 1);
	continue;
}

while ($t) {
	$a++;
}

$i = 0;
do {
	echo $i;
} while ($i > 0);

foreach (array(1,2,3) as $i => $item) {
	echo $i;
}

$i=0;
switch ($i) {
	case $t:
		echo "i=0";
		break;
	case 1:
		echo "i=1";
		break;
	default:
		echo "i=2";
		break;
}

declare(ticks=1) {

}