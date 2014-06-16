<?php


/**
 * Decoder for RLE4 compression in windows bitmaps
 * see http://msdn.microsoft.com/library/default.asp?url=/library/en-us/gdi/bitmaps_6x0u.asp
 *
 * @param string  $str   Data to decode
 * @param integer $width Image width
 *
 * @return string
 */
function rle4_decode ($str, $width) {
	$w = floor($width/2) + ($width % 2);
	$lineWidth = $w + (3 - ( ($width-1) / 2) % 4);
	$pixels = array();
	$cnt = strlen($str);
	$c = 0;

	for ($i = 0; $i < $cnt; $i++) {
		$o = ord($str[$i]);
		switch ($o) {
			case 0: # ESCAPE
				$i++;
				switch (ord($str[$i])){
					case 0: # NEW LINE
						while (count($pixels)%$lineWidth != 0) {
							$pixels[] = 0;
						}
						break;
					case 1: # END OF FILE
						while (count($pixels)%$lineWidth != 0) {
							$pixels[] = 0;
						}
						break 3;
					case 2: # DELTA
						$i += 2;
						break;
					default: # ABSOLUTE MODE
						$num = ord($str[$i]);
						for ($j = 0; $j < $num; $j++) {
							if ($j%2 == 0) {
								$c = ord($str[++$i]);
								$pixels[] = ($c & 240)>>4;
							}
							else {
								$pixels[] = $c & 15;
							}
						}

						if ($num % 2 == 0) {
							$i++;
						}
				}
				break;
			default:
				$c = ord($str[++$i]);
				for ($j = 0; $j < $o; $j++) {
					$pixels[] = ($j%2==0 ? ($c & 240)>>4 : $c & 15);
				}
		}
	}

	$out = '';
	if (count($pixels)%2) {
		$pixels[] = 0;
	}

	$cnt = count($pixels)/2;

	for ($i = 0; $i < $cnt; $i++) {
		$out .= chr(16*$pixels[2*$i] + $pixels[2*$i+1]);
	}

	return $out;
}