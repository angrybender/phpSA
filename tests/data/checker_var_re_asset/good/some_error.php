<?php

/**
 * Возвращает тело сущности и найденные в нем матчи/ссылки
 *
 * @version $Id: class.ApiExtractMatches.php 2140 2012-09-27 07:02:29Z vGhost $
 */
class ApiExtractMatches extends ApiBase
{
	/**
	 * Функция возвращает тело сущности и список матчей и мап
	 *
	 * Возможные параметры в $request:
	 *
	 * 	int    entity_id   - идентификатор сущности
	 * 	int    revision_id - идентификатор ревизии документа
	 *  string body        - тело сущности для тестирования
	 *  bool   skip        - пропустить девятый шаг (сбор терминов)
	 *  bool   debug       - отладочный вывод с байндингом ссылок на текст сущности
	 *
	 *
	 * @param array $request
	 *
	 * @return mixed
	 */
	public function execute($request)
	{
		$result = array(
			'status' => self::RESULT_NO_DATA
		);
		$entity = array(
			'body' 				=> empty($request['body']) ? '' : $request['body'],
			'document_id' 		=> 0,
			'entity_id' 		=> 0,
			'line_id' 			=> 0,
			'line_id_parent'	=> 0,
			'type_id' 			=> 0,
			'tree_path' 		=> 0,
			'type_path' 		=> 0,
			'last_revision_id' 	=> 0,
		);
		if (!empty($request['skip'])) {
			$skip_step = array(
				RelationConstant::STEP_NINE_DESCR,
			);
		} else {
			$skip_step = array();
		}
		if (!empty($request['entity_id'])) {
			$entity_id = $request['entity_id'];
			$entity_body = Entity::getBodies(array($entity_id));
			if (!empty($entity_body[$entity_id])) {
				$entity = Entity::get($entity_id);
				$entity['body'] = $entity_body[$entity_id];
			}
		}

		$debug = !empty($request['debug']);

		if ($entity['body']) {
			// Граббинг
			try {
				$matches = array();
				if (count($entity) != 1) {
					$processor = new RelationsGrabber();
					$matches = $processor->findRelations($entity, $skip_step);
				}
				$relations = array();
				if ($matches) {
					// Маппинг
					$relations = array_shift(Aggregator::analyzeRelations($matches, true));
					foreach ($matches as $i => &$match) {
						$match['match_id'] = $i;
						$match['data'] = unserialize($match['data']);
					}
					if ($relations) {
						foreach ($relations as $i => &$relation) {
							$relation['relation_id'] = $i;
						}
					}
				}
				if ($relations) {
					$revision_id = !empty($request['revision_id']) ? $request['revision_id'] : $entity['last_revision_id'];
					$relations = DocumentRelationsMap::getEntitiesForRelations($relations, $revision_id);
				}
				$data = array(
					'body' => $entity['body'],
				);
				if ($debug) {
					foreach ($relations as &$map) {
						$map['document_id'] = &$map['slave_doc_id'];
						$map['revision_id'] = &$map['slave_revision_id'];
						$map['line_id']     = &$map['slave_line_id'];
						$map['entity_id']   = &$map['slave_id'];
					}
					$binded_body = Binder::bind($entity['body'], $relations);
					$binded_body = preg_replace('~(http://[^/]+/)?entity/get/~S', 'http://docs.pravo.ru/document/view/', $binded_body);
					$data['binded_body'] = $binded_body;
					foreach ($relations as &$map) {
						unset(
						$map['document_id'],
						$map['revision_id'],
						$map['line_id'],
						$map['entity_id']
						);
					}
					foreach ($matches as &$match) {
						if (!empty($match['data'])) {
							if (is_string($match['data'])) {
								$match['data'] = unserialize($match['data']);
							} elseif (!empty($match['data']['submatches'])) {
								foreach ($match['data']['submatches'] as &$m) {
									if (is_string($m['data'])) {
										$m['data'] = unserialize($m['data']);
									}
								}
							}
						}
					}
				}
				$data['matches']   = $matches;
				$data['relations'] = $relations;
				$result = array(
					'data' => $data
				);
			} catch (Exception $e) {
				$result = array(
					'status' => self::RESULT_FAILURE,
				);
				$this->addException($e);
			}
		}

		return $this->setResult($result);
	}
}
