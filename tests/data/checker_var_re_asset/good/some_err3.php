<?php


function stepGlueSubmatch(&$relation, &$sub_params, $slave_doc_id, $slave_type_id, &$submatches, $master_date = false)
{
	$entities = array();

	// Порядковый номер сущности
	$number = -1;

	// Проверяем - есть ли слово часть в пути - после статьи.
	// Зачастую в кодексах его используют вместо пунктов, поэтому мы просто переставим тип
	// Также проверяем на FLA-51 - "В третьем предложении пункта 1 части 5"
	// Ищем указатель номера предложения
	if (!empty($sub_params['type_path'])) {
		$type_pathes   = explode(',', $sub_params['type_path']);
		$article_found = false;
		if (count($type_pathes) > 1) {
			foreach ($type_pathes as $key => &$_type) {
				if (EntityConstant::TYPE_ARTICLE == $_type) {
					$article_found = true;
				} else if (EntityConstant::TYPE_PART == $_type && $article_found) {
					$_type = EntityConstant::TYPE_CLAUSE;
				}
				// Указатель на номер предложения
				else if (EntityConstant::TYPE_NO_NAME == $_type) {
					$pathes = explode(',', $sub_params['path']);
					if ($pathes[$key] > 1) {
						$number = $pathes[$key];
						unset($pathes[$key]);
						$sub_params['path'] = implode(',', $pathes);
					}
					unset($pathes);
				}
			}
			$sub_params['type_path'] = implode(',', $type_pathes);
		}
		unset($_type, $type_pathes);
	}
}