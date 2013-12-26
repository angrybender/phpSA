<?php
/**
 *
 * @author k.vagin
 */

function query($id)
{
	$ar_result = mysql_query("SELECT * FROM `table` where id = {$id}");
	return $ar_result;
}