<?php
/**
 *
 * @author k.vagin
 */

public function __construct($params)
{
	$this->CI =& get_instance();
	extract($params);

	if ($autoload === TRUE)
	{
		$this->script();
	}

	log_message('debug', "Jquery Class Initialized");
}