<?php
/**
 *
 * @author k.vagin
 */

function toHTML(){
	return "<pre>".var_export($this->data, true)."</pre>";
}