<?php
/**
 *
 * @author k.vagin
 */

function _wrapField($namespace, $field_name, $rendered)
{
	$namespace = $this->_getWrapperPart($namespace);
	$field_name = $this->_getWrapperPart($field_name);
	$wrapper_id = $namespace . '_' . $field_name;
	return '<div id="' . $wrapper_id . '">' . $rendered . '<!--' . $wrapper_id . '--></div>';
}