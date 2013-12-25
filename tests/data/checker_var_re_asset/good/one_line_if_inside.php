<?php
/**
 *
 * @author k.vagin
 */

protected function _smtp_connect()
{
	$ssl = NULL;
	if ($this->smtp_crypto == 'ssl')
		$ssl = 'ssl://';
}