<?php

function _betaFraction($x, $p, $q) {
	$c = 1.0;
	$sum_pq = $p + $q;
	$p_plus = $p + 1.0;
	$p_minus = $p - 1.0;
	$h = 1.0 - $sum_pq * $x / $p_plus;
	if (abs($h) < XMININ) {
		$h = XMININ;
	}
	$h = 1.0 / $h;
	$frac = $h;
	$m	 = 1;
	$delta = 0.0;
	while ($m <= MAX_ITERATIONS && abs($delta-1.0) > PRECISION ) {
		$m2 = 2 * $m;
		// even index for d
		$d = $m * ($q - $m) * $x / ( ($p_minus + $m2) * ($p + $m2));
		$h = 1.0 + $d * $h;
		if (abs($h) < XMININ) {
			$h = XMININ;
		}
		$h = 1.0 / $h;
		$c = 1.0 + $d / $c;
		if (abs($c) < XMININ) {
			$c = XMININ;
		}
		$frac *= $h * $c;
		// odd index for d
		$d = -($p + $m) * ($sum_pq + $m) * $x / (($p + $m2) * ($p_plus + $m2));
		$h = 1.0 + $d * $h;
		if (abs($h) < XMININ) {
			$h = XMININ;
		}
		$h = 1.0 / $h;
		$c = 1.0 + $d / $c;
		if (abs($c) < XMININ) {
			$c = XMININ;
		}
		$delta = $h * $c;
		$frac *= $delta;
		++$m;
	}
	return $frac;
}	//	function _betaFraction()