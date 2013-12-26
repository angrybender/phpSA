<?php
/**
 *
 * @author k.vagin
 */

foreach ($filters->filter as $filterRule) {
	$column->createRule()->setRule(
		NULL,	//	Operator is undefined, but always treated as EQUAL
		(string) $filterRule["val"]
	)
		->setRuleType(PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_FILTER);
}