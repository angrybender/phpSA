<?php

/* validate for invalid data */
if (!empty($category)) {
	$category = $modx->getObject('modCategory',$scriptProperties['target']);
	if (empty($category)) $modx->error->addField('target',$modx->lexicon('category_err_nf'));
	if (!$category->checkPolicy('view')) $modx->error->addField('target',$modx->lexicon('access_denied'));
}