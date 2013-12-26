<?php
/**
 *
 * @author k.vagin
 */

function _parseEmails($list)
{
	$list = \CString::strtolower($list);
	$ar_result = preg_split('/[,\;\s]/', $list); // запятая, точка с запятой, пробел, энтер, таб.
	// всякие ивзращения с отступами вроде mail1@mail.ru   ,    mail2@mail.ru нормализует фильтр ниже
	$ar_result = array_filter($ar_result);

	return $ar_result;
}