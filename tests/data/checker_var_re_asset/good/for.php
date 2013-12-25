<?php
/**
 *
 * @author k.vagin
 */

function _travelNode(&$node, &$data)
{
	foreach ($node as $key => $value) {
		if (count($value->children()) > 0) {
			$attrs = $value->attributes();
			if (is_array($data) && array_key_exists($key, $data)) {
				$i = self::_makeArray($data, $key);
				self::_travelNode($value, $data[$key][$i]);
			} else {
				self::_travelNode($value, $data[$key]);
			}
			if (count($attrs) > 0) {
				self::_parseAttributes($key, $attrs, $data);
			}
		} elseif (is_array($data) && array_key_exists($key, $data)) {
			$attrs = $value->attributes();
			$i = self::_makeArray($data, $key);
			if (count($attrs) > 0) {
				self::_parseAttributes($i, $attrs, $data[$key]);
			} else {
				$value = (string) $value;
				if (!empty($value)) {
					self::_setValue($data[$key], $i, $value);
				}
			}
		} else {
			$attrs = $value->attributes();
			if (count($attrs) > 0) {
				self::_parseAttributes($key, $attrs, $data);
			} else {
				self::_setValue($data, $key, $value);
			}
		}
	}
}