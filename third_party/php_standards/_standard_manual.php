<?php

/**
 * @param string $str The string being translated.
 * @param array $replace_pairs The replace_pairs parameter may be used as a substitute for to and from in which case it's an array in the form array('from' => 'to', ...).
 * @return string A copy of str, translating all occurrences of each character in from to the corresponding character in to.
 */
function strtr ($str, array $replace_pairs) {}

/**
 * PHP >= 5.4.0<br/>
 * Convert hex to binary
 * @link http://php.net/manual/en/function.hex2bin.php
 * @param string $data
 * @return string Returns the binary representation of the given data.
 * @see bin2hex(), unpack()
 */
function hex2bin($data) {}

/**
 * Get or Set the HTTP response code
 * @param int $response_code [optional] The optional response_code will set the response code.
 * @return int The current response code. By default the return value is int(200).
 */
function http_response_code($response_code) {}