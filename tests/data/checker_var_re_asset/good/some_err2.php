<?php


function stepFourth($relation, $recursive = false)
{
	if (empty($relation['document_id'])) {
		$document_info_master = null;
	} else {
		$document_info_master = Document::getDocumentInfo($relation['document_id']);
	}
	$params = unserialize($relation['data']);
	$data   = array();

	// Ссылки в кавычках всегда ссылаются на текущий документ
	if (!empty($params['bracketed'])) {
		// Так и есть, прийдется сформировать slave
		// Найдем сущность-хидер ткущего документ для нужной нам ревизии
		// Тут не важны ревизии
		if ($slave = Entity::getTitleIdByDocId($relation['document_id'])) {
			$slave = array_shift($slave);
			$slave['document_id'] = $relation['document_id'];
		}
	} else {
		// Ищем есть ли линки с этого entity на документ (справа - то есть "пункт 1 закона № 3232 от 4.12.2003 г")
		$skip_priority = array(
			RelationConstant::STEP_NINE_SINGLELINE_PRIORITY,
			RelationConstant::STEP_NINE_MULTILINE_PRIORITY,
			RelationConstant::STEP_NINE_TOOLTIP_PRIORITY,
		);
		$slave = self::findNeighbour($relation['entity_id'], $params['start'], (empty($params['document_id_left'])
			|| $params['document_id_left'] === false), $skip_priority);
	}
}