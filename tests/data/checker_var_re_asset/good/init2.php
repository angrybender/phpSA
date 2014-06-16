<?php

function _buildWorkbookEscher()
{
	$escher = array();

	// any drawings in this workbook?
	$found = false;
	foreach ($this->_phpExcel->getAllSheets() as $sheet) {
		if (count($sheet->getDrawingCollection()) > 0) {
			$found = true;
			break;
		}
	}

	// nothing to do if there are no drawings
	if (!$found) {
		return;
	}

	// if we reach here, then there are drawings in the workbook
	$escher = new PHPExcel_Shared_Escher();
}