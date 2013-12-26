<?php
/**
 *
 * @author k.vagin
 */

function query($field)
{
	$ar_result = mysql_query("SELECT * FROM `table` where id = {$_GET[$field]}");
	return $ar_result;
}