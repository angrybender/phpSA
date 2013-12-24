<?php
/**
 *
 * @author k.vagin
 */

function _output($array_js = '')
{
	if ( ! is_array($array_js))
	{
		$array_js = array($array_js);
	}

	foreach ($array_js as $js)
	{
		$this->jquery_code_for_compile[] = "\t$js\n";
	}
}