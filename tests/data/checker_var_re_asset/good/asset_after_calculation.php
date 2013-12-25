<?php
/**
 *
 * @author k.vagin
 */

function _add_data($filepath, $data, $file_mtime, $file_mdate)
{
	$filepath = str_replace("\\", "/", $filepath);

	$uncompressed_size = strlen($data);
	$crc32  = crc32($data);

	$gzdata = gzcompress($data);
	$gzdata = substr($gzdata, 2, -4);
	$compressed_size = strlen($gzdata);

	$this->zipdata .=
		"\x50\x4b\x03\x04\x14\x00\x00\x00\x08\x00"
		.pack('v', $file_mtime)
		.pack('v', $file_mdate)
		.pack('V', $crc32)
		.pack('V', $compressed_size)
		.pack('V', $uncompressed_size)
		.pack('v', strlen($filepath)) // length of filename
		.pack('v', 0) // extra field length
		.$filepath
		.$gzdata; // "file data" segment

	$this->directory .=
		"\x50\x4b\x01\x02\x00\x00\x14\x00\x00\x00\x08\x00"
		.pack('v', $file_mtime)
		.pack('v', $file_mdate)
		.pack('V', $crc32)
		.pack('V', $compressed_size)
		.pack('V', $uncompressed_size)
		.pack('v', strlen($filepath)) // length of filename
		.pack('v', 0) // extra field length
		.pack('v', 0) // file comment length
		.pack('v', 0) // disk number start
		.pack('v', 0) // internal file attributes
		.pack('V', 32) // external file attributes - 'archive' bit set
		.pack('V', $this->offset) // relative offset of local header
		.$filepath;

	$this->offset = strlen($this->zipdata);
	$this->entries++;
	$this->file_num++;
}