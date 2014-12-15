<?php
/**
 *
 * @author k.vagin
 */

$a = 1;
$b = false;
$c = array(1, 'a' => $a+1);
$d = $a/2;
$r = 1*5;

$div = $d % 2;

$r = -$a;

$b++;
$c--;
++$b;
--$c;

$srt = 'a' . $a;

$bin = $a | $b;
$bin = $a & $b;
$bin = $a ^ $b;
$bin = $a << $b;
$bin = $a >> $b;
$bin = ~$b;