<?php
/**
 *
 * @author k.vagin
 */

//Обработка буквы F собственным механизмом
if (strpos($format, 'F') !== false) {
	$format = str_replace("F", $this->_getMonth("long"), $format);
}

//Обработка буквы M собственным механизмом
if (strpos($format, 'M') !== false) {
	$format = str_replace("M", $this->_getMonth("short"), $format);
}