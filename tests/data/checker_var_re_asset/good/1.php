<?php

function t() {
$subtype = strtolower($subtype_raw);

if ( $family_raw ) {
	$family = str_replace( array("'", '"'), "", strtolower($family_raw));

	if ( isset(self::$_font_lookup[$family][$subtype]) ) {
		return $cache[$family_raw][$subtype_raw] = self::$_font_lookup[$family][$subtype];
	}

	return null;
}

$family = "serif";

if ( isset(self::$_font_lookup[$family][$subtype]) ) {
	return $cache[$family_raw][$subtype_raw] = self::$_font_lookup[$family][$subtype];
}

if ( !isset(self::$_font_lookup[$family]) ) {
	return null;
}

$family = self::$_font_lookup[$family];

foreach ( $family as $sub => $font ) {
	if (strpos($subtype, $sub) !== false) {
		return $cache[$family_raw][$subtype_raw] = $font;
	}
}

if ($subtype !== "normal") {
	foreach ( $family as $sub => $font ) {
		if ($sub !== "normal") {
			return $cache[$family_raw][$subtype_raw] = $font;
		}
	}
}

$subtype = "normal";
}