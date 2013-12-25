<?php
/**
 *
 * @author k.vagin
 */

function field_data()
{
	$data = array();

	try
	{
		for($i = 0; $i < $this->num_fields(); $i++)
		{
			$data[] = $this->result_id->getColumnMeta($i);
		}

		return $data;
	}
	catch (Exception $e)
	{
		if ($this->db->db_debug)
		{
			return $this->db->display_error('db_unsuported_feature');
		}
		return FALSE;
	}
}