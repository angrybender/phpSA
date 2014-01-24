<?php
/**
 *
 * @author k.vagin
 */

public function getRelationsByRevisionId($params)
{
	// Если запрос пришел не по адресу
	if (empty($params['revision_id'])) {
		return array();
	}

	$revision_id = $params['revision_id'];

	$query = self::makeQuery($params, $revision_id);
	$relations = self::$db->getList($query['sql'], $query['query_params']);
}