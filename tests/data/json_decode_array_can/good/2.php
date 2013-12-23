<?php
/**
 *
 * @author k.vagin
 */

$this->descriptor = $descriptor = json_decode(file_get_contents($descriptorFile), true);
if(!json_decode(file_get_contents($descriptorFile))) {
	throw new Exception('Package descriptor for program not valid JSON: ' . $descriptorFile);
}