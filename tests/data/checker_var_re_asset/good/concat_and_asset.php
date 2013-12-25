<?php
/**
 *
 * @author k.vagin
 */

function _parsePath($path)
{
	$result = '$';

	if ($path) {
		$paths = explode('.', $path);

		$result .= array_shift($paths);

		if (is_array($paths)) {
			foreach ($paths as $_path) {
				$result .= "['" . $_path . "']";
			}
		}
	}

	return $result;
}