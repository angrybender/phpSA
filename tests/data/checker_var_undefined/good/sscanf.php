<?php
/**
 *
 * @author k.vagin
 */

function getSortedCellList() {
	$sortKeys = array();
	foreach ($this->getCellList() as $coord) {
		sscanf($coord,'%[A-Z]%d', $column, $row);
		$sortKeys[sprintf('%09d%3s',$row,$column)] = $coord;
	}
	ksort($sortKeys);

	return array_values($sortKeys);
}	//	function sortCellList()

function extractAllCellReferencesInRange($pRange = 'A1') {
	// Returnvalue
	$returnValue = array();

	// Explode spaces
	$cellBlocks = explode(' ', str_replace('$', '', strtoupper($pRange)));
	foreach ($cellBlocks as $cellBlock) {
		// Single cell?
		if (strpos($cellBlock,':') === FALSE && strpos($cellBlock,',') === FALSE) {
			$returnValue[] = $cellBlock;
			continue;
		}

		// Range...
		$ranges = self::splitRange($cellBlock);
		foreach($ranges as $range) {
			// Single cell?
			if (!isset($range[1])) {
				$returnValue[] = $range[0];
				continue;
			}

			// Range...
			list($rangeStart, $rangeEnd)	= $range;
			sscanf($rangeStart,'%[A-Z]%d', $startCol, $startRow);
			sscanf($rangeEnd,'%[A-Z]%d', $endCol, $endRow);
			$endCol++;

			// Current data
			$currentCol	= $startCol;
			$currentRow	= $startRow;

			// Loop cells
			while ($currentCol != $endCol) {
				while ($currentRow <= $endRow) {
					$returnValue[] = $currentCol.$currentRow;
					++$currentRow;
				}
				++$currentCol;
				$currentRow = $startRow;
			}
		}
	}

	//	Sort the result by column and row
	$sortKeys = array();
	foreach (array_unique($returnValue) as $coord) {
		sscanf($coord,'%[A-Z]%d', $column, $row);
		$sortKeys[sprintf('%3s%09d',$column,$row)] = $coord;
	}
	ksort($sortKeys);

	// Return value
	return array_values($sortKeys);
}