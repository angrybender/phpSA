<?php
/**
 *
 * @author k.vagin
 */

function image_process_imagemagick($action = 'resize')
{
	//  Do we have a vaild library path?
	if ($this->library_path == '')
	{
		$this->set_error('imglib_libpath_invalid');
		return FALSE;
	}

	if ( ! preg_match("/convert$/i", $this->library_path))
	{
		$this->library_path = rtrim($this->library_path, '/').'/';

		$this->library_path .= 'convert';
	}

	// Execute the command
	$cmd = $this->library_path." -quality ".$this->quality;

	if ($action == 'crop')
	{
		$cmd .= " -crop ".$this->width."x".$this->height."+".$this->x_axis."+".$this->y_axis." \"$this->full_src_path\" \"$this->full_dst_path\" 2>&1";
	}
	elseif ($action == 'rotate')
	{
		switch ($this->rotation_angle)
		{
			case 'hor'	: $angle = '-flop';
				break;
			case 'vrt'	: $angle = '-flip';
				break;
			default		: $angle = '-rotate '.$this->rotation_angle;
			break;
		}

		$cmd .= " ".$angle." \"$this->full_src_path\" \"$this->full_dst_path\" 2>&1";
	}
	else  // Resize
	{
		$cmd .= " -resize ".$this->width."x".$this->height." \"$this->full_src_path\" \"$this->full_dst_path\" 2>&1";
	}

	$retval = 1;

	@exec($cmd, $output, $retval);

	//	Did it work?
	if ($retval > 0)
	{
		$this->set_error('imglib_image_process_failed');
		return FALSE;
	}

	// Set the file to 777
	@chmod($this->full_dst_path, FILE_WRITE_MODE);

	return TRUE;
}