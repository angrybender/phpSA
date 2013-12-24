<?php
/**
 *
 * @author k.vagin
 */

function _animate($element = 'this', $params = array(), $speed = '', $extra = '')
{
	$element = $this->_prep_element($element);
	$speed = $this->_validate_speed($speed);

	$animations = "\t\t\t";

	foreach ($params as $param=>$value)
	{
		$animations .= $param.': \''.$value.'\', ';
	}

	$animations = substr($animations, 0, -2); // remove the last ", "

	if ($speed != '')
	{
		$speed = ', '.$speed;
	}

	if ($extra != '')
	{
		$extra = ', '.$extra;
	}

	$str  = "$({$element}).animate({\n$animations\n\t\t}".$speed.$extra.");";

	return $str;
}