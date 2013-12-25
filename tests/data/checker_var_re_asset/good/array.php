<?php
/**
 *
 * @author k.vagin
 */

function _parseAttributes($node_name, $attrs, &$data)
{
	if (isset($attrs['value'])) {
		self::_setValue($data, $node_name, $attrs['value']);
	} elseif (isset($attrs['mount'])) {
		$mount = explode('/', (string)$attrs['mount']);
		if ($mount[1]
			&& (!isset(self::$_includes[end(self::$_files_stack)])
				|| !in_array($mount[0], self::$_includes[end(self::$_files_stack)]))) {
			self::$_includes[end(self::$_files_stack)][] = $mount[0];
		}
		self::_setValue($data, $node_name, '::' . $mount[1]);
	} else {
		foreach ($attrs as $ka => $va) {
			self::_setValue($data[$node_name], $ka, $va);
		}
	}
}