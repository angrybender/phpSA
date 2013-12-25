<?php
/**
 *
 * @author k.vagin
 */

function db_pconnect()
{
	if ( ! $conn_id = @sqlite_popen($this->database, FILE_WRITE_MODE, $error))
	{
		log_message('error', $error);

		if ($this->db_debug)
		{
			$this->display_error($error, '', TRUE);
		}

		return FALSE;
	}

	return $conn_id;
}