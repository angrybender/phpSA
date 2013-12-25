<?php
/**
 *
 * @author k.vagin
 */
function process($doc, $documents)
{
	static $results;
	if (!$results) {
		$results = @file_get_contents(MCD . '00000_results.txt');
	}

	$res = strpos($results, '0' . $doc) !== false ? 0 : strpos($results, '1' . $doc) !== false;
	if ($res === false) {
		$regexp = '#' . preg_quote(date('d.m.Y', strtotime($documents[0]['date_title'] . ' 00:00:00'))) .
			'\s+№\s+' . preg_quote($documents[0]['number']) . '\s+' . preg_quote($documents[0]['title']) . '#usi'
		;
		if (preg_match($regexp, $doc) !== false) {
			// удалось определить автоматически
			$res = 0;
		} else {
			// если автоматом не вышло, спросим юзера
			passthru(
				'dialog --title "Соответствует действительности?" --clear --yesno "' . $doc . '\n----------------\n\n' .
				$documents[0]['title'] . '\ndate_accept ' . $documents[0]['date_accept'] . '\ndate_title ' .
				$documents[0]['date_title'] . '\ndate_activate ' . $documents[0]['date_activate'] . '\nnumber ' .
				$documents[0]['number'] . '" 15 100'
				, $res
			);
		}
		//$res == 0 - Yes
		file_put_contents(MCD . '00000_results.txt', $res . $doc . "\n", FILE_APPEND);
	}

	if ($res === 0) {
		global $document_types;
		$numb    = $documents[0]['document_id'];
		$name    = preg_quote($documents[0]['title']);
		$type_id = $documents[0]['type_id'];
		$type    = isset($document_types[$documents[0]['type_id']])
			? $document_types[$documents[0]['type_id']] : 'other';
		return array($numb => compact('type', 'type_id', 'doc', 'name', 'numb'));
	}
	return array();
}