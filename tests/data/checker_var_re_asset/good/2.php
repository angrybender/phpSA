<?php

function save_font_families() {
	// replace the path to the DOMPDF font directories with the corresponding constants (allows for more portability)
	$cache_data = var_export(self::$_font_lookup, true);
	$cache_data = str_replace('\''.DOMPDF_FONT_DIR , 'DOMPDF_FONT_DIR . \'' , $cache_data);
	$cache_data = str_replace('\''.DOMPDF_DIR , 'DOMPDF_DIR . \'' , $cache_data);
	$cache_data = "<"."?php return $cache_data ?".">";
	file_put_contents(self::CACHE_FILE, $cache_data);
}