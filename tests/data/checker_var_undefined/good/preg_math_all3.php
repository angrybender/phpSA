<?php
/**
 *
 * @author k.vagin
 */

function set_transform($val) {
	$number   = "\s*([^,\s]+)\s*";
	$tr_value = "\s*([^,\s]+)\s*";
	$angle    = "\s*([^,\s]+(?:deg|rad)?)\s*";

	if ( !preg_match_all("/[a-z]+\([^\)]+\)/i", $val, $parts, PREG_SET_ORDER) ) {
		return;
	}

	$functions = array(
		//"matrix"     => "\($number,$number,$number,$number,$number,$number\)",

		"translate"  => "\($tr_value(?:,$tr_value)?\)",
		"translateX" => "\($tr_value\)",
		"translateY" => "\($tr_value\)",

		"scale"      => "\($number(?:,$number)?\)",
		"scaleX"     => "\($number\)",
		"scaleY"     => "\($number\)",

		"rotate"     => "\($angle\)",

		"skew"       => "\($angle(?:,$angle)?\)",
		"skewX"      => "\($angle\)",
		"skewY"      => "\($angle\)",
	);

	$transforms = array();

	foreach($parts as $part) {
		$t = $part[0];

		foreach($functions as $name => $pattern) {
			if ( preg_match("/$name\s*$pattern/i", $t, $matches) ) {
				$values = array_slice($matches, 1);

				switch($name) {
					// <angle> units
					case "rotate":
					case "skew":
					case "skewX":
					case "skewY":

						foreach($values as $i => $value) {
							if ( strpos($value, "rad") ) {
								$values[$i] = rad2deg(floatval($value));
							}
							else {
								$values[$i] = floatval($value);
							}
						}

						switch($name) {
							case "skew":
								if ( !isset($values[1]) ) {
									$values[1] = 0;
								}
								break;
							case "skewX":
								$name = "skew";
								$values = array($values[0], 0);
								break;
							case "skewY":
								$name = "skew";
								$values = array(0, $values[0]);
								break;
						}
						break;

					// <translation-value> units
					case "translate":
						$values[0] = $this->length_in_pt($values[0], $this->width);

						if ( isset($values[1]) ) {
							$values[1] = $this->length_in_pt($values[1], $this->height);
						}
						else {
							$values[1] = 0;
						}
						break;

					case "translateX":
						$name = "translate";
						$values = array($this->length_in_pt($values[0], $this->width), 0);
						break;

					case "translateY":
						$name = "translate";
						$values = array(0, $this->length_in_pt($values[0], $this->height));
						break;

					// <number> units
					case "scale":
						if ( !isset($values[1]) ) {
							$values[1] = $values[0];
						}
						break;

					case "scaleX":
						$name = "scale";
						$values = array($values[0], 1.0);
						break;

					case "scaleY":
						$name = "scale";
						$values = array(1.0, $values[0]);
						break;
				}

				$transforms[] = array(
					$name,
					$values,
				);
			}
		}
	}

	//see __set and __get, on all assignments clear cache, not needed on direct set through __set
	$this->_prop_cache["transform"] = null;
	$this->_props["transform"] = $transforms;
}