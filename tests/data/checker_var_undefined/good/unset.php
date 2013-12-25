<?php
/**
 *
 * @author k.vagin
 */

public function batch_bcc_send()
{
	$float = $this->bcc_batch_size -1;

	$set = "";

	$chunk = array();

	for ($i = 0; $i < count($this->_bcc_array); $i++)
	{
		if (isset($this->_bcc_array[$i]))
		{
			$set .= ", ".$this->_bcc_array[$i];
		}

		if ($i == $float)
		{
			$chunk[] = substr($set, 1);
			$float = $float + $this->bcc_batch_size;
			$set = "";
		}

		if ($i == count($this->_bcc_array)-1)
		{
			$chunk[] = substr($set, 1);
		}
	}

	for ($i = 0; $i < count($chunk); $i++)
	{
		unset($bcc, $this->_headers['Bcc']);

		$bcc = $this->_str_to_array($chunk[$i]);
		$bcc = $this->clean_email($bcc);

		if ($this->protocol != 'smtp')
		{
			$this->_set_header('Bcc', implode(", ", $bcc));
		}
		else
		{
			$this->_bcc_array = $bcc;
		}

		$this->_build_message();
		$this->_spool_email();
	}
}