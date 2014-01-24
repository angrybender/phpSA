<?php
/**
 *
 * @author k.vagin
 */

function findRelations(array $entity)
{
	$body			= &$entity['body'];
	$entity_id		= &$entity['entity_id'];
	$document_id	= &$entity['document_id'];
	$line_id		= &$entity['line_id'];
	$line_id_parent	= &$entity['line_id_parent'];
	$type_id		= &$entity['type_id'];
	$revision_id	= &$entity['revision_id'];
	$document_type	= &$entity['document_type_id'];
	$clear_path		= &$entity['clear_path'];
	$type_path		= &$entity['type_path'];
	$tree_path		= &$entity['tree_path'];

	// Запрашиваем из БД доп. свойства документа, в частности заголовки словарей
	$document_properties = self::getDocumentProperties($document_id);
	if (!empty($document_properties['abbreviation'])) {
		// Если в БД уже есть какие-то заголовки словарей,
		// поищем среди них текущую сущность
		foreach ($document_properties['abbreviation'] as $val) {
			if ($val['entity_id'] == $entity_id) {
				// Нашли. Больше ловить нечего
				return $this->results;
			}
		}
	}

	// Может быть это новый заголовок словаря?
	// Например,"В настоящем справочнике используются следующие основные понятия:"
	if (self::checkBodyForDictionaryHeader($body, $docTypeId, $typeId)) {
		// Похоже на заголовок словаря
		// Ищем входящие в него [словарь] определения терминов (дочерние сущности)
		$terms = Entity::getByUpperLevelLineId($document_id, $line_id);

		if (!$terms) {
			// нет дочерних сущностей
			// ищем сущности одного уровня
			$terms = Entity::getByUpperLevelLineId($document_id, $line_id_parent);
			if (!$terms) {
				// Термины не найдены
				return $this->results;
			}

			// Ищем сущность заголовка словаря
			// Нам нужны сущности после нее
			$pos = -1;
			foreach ($terms as $key => $ent) {
				if ($ent['entity_id'] == $entity_id) {
					$pos = $key+1;
					break;
				}
			}
			if ($pos === -1) {
				// Ошибка - не найден заголовок словаря
				return $this->results;
			}

			$terms = array_slice($terms, $pos);

			if (count($terms) < 1) {
				// есть только заголовок словаря (или то, что на него похоже)
				// Больше ловить нечего
				return $this->results;
			}
		}

		// Разбираем термины этого словаря, сохраняем в БД
		// N.B. Поскольку мы не доходим до этого шага, если заголовок словаря уже есть в БД,
		//		в случае добавления новых терминов в словарь в новых ревизиях,
		//		пересчёт нужно будет запускать с помощью специального консьюмера.
		foreach ($terms as $term) {
			self::findAbbreviations($term);
		}

		// Сохраняем заголовок словаря в базе, вместе с информацией о сущности
		$vocabulary_header = array(
			'document_id'	 => $document_id,
			'revision_id'	 => $revision_id,
			'entity_id'		 => $entity_id,
			'line_id'		 => $line_id,
			'line_id_parent' => $line_id_parent,
			'type_path'		 => $type_path,
			'clear_path'	 => $clear_path,
			'tree_path'		 => $tree_path,
		);
		$document_properties['abbreviation'][] = $vocabulary_header;
		self::updateDocumentProperty($document_id, 'abbreviation', $vocabulary_header);

		return $this->results;
	}

	// Это не заголовок словаря, ищем использование словарных терминов в сущности

	// найдём первые две буквы всех слов
	$regexp = '~' . SimpleStemmer::$_regexp_lw . '([а-яА-ЯёЁ]{2})[\-а-яА-ЯёЁ]*~u';
	preg_match_all($regexp, $body, $matches);
	// приводим все найденные буквы к нижнему регистру
	$first_letters = array_map(array('CString', 'strtolower'), $matches[1]);
	//убираем повторы
	$first_letters = array_unique($first_letters);

	// Поиск похожих сокращений в словаре
	$abbreviations = self::getAbbreviations($first_letters);
	foreach ($abbreviations as &$abbr_fl_list) { // для каждого двухбуквенного сочетания
		foreach ($abbr_fl_list as $abbr) { // для каждого термина
			// Приводим стем к валидному виду для корректной работы внутри регулярки
			$stem = str_replace('\[', '½@', $abbr['stem']);
			$stem = str_replace('\]', '½®', $stem);
			if (substr_count($stem, '[') != substr_count($stem, ']')) {
				do {
					$stem = substr($stem, 0, -1);
				} while (substr_count($stem, '[') != substr_count($stem, ']'));
				$stem = str_replace('½@', '\[', $stem);
				$stem = str_replace('½®', '\]', $stem);
			} else {
				$stem = $abbr['stem'];
			}
			// разворачиваем стем термина в регулярку
			$unstem = str_replace('', SimpleStemmer::$_end.'?', $stem);
			// регулярное выражение для сокращения
			$abbr_regexp = '/' . self::$_regexp_lw . $unstem . self::$_regexp_rw . '/u';
			// ищем совпадения
			//  выкинул условие CString::strlen($abbr['stem']) < 1000 &&
			if (preg_match($abbr_regexp, $body, $match, PREG_OFFSET_CAPTURE)) {
				$_start = self::convertOffset($body, $match[0][1]);
				$_end = $_start + CString::strlen($match[0][0]);

				if ($abbr['entity_id'] == $entity_id) {
					// ссылка на текущую сущность (данная сущность является определением термина)
					// пропускаем
					continue;
				}

				$this->results[] = array(
					'document_id' => &$document_id,
					'entity_id'   => &$entity_id,
					'revision_id' => &$revision_id,
					'step'        => RelationConstant::STEP_NINE_DESCR,
					'line_id'     => &$line_id,
					'data'        => serialize(array(
						'document_type_id'   => &$document_type,
						'type_id'            => &$type_id,
						'start'              => $_start,
						'end'                => $_end,
						'match'              => $match[0][0],
						'abbr_entity_id'     => $abbr['entity_id'],
						'abbr_title'         => $abbr['title'],
						'abbr_document_id'   => $abbr['document_id'],
						'abbr_document_type' => $abbr['document_type'],
						'abbr_revision_id'   => $abbr['revision_id'],
						'abbr_line_id'       => $abbr['line_id'],
						'abbr_type_id'       => $abbr['type_id'],
						'abbr_type'          => $abbr['type'],
					)),
				);
			}
		}
	}

	return $this->results;
}