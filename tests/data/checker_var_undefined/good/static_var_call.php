<?php
/**
 *
 * @author k.vagin
 */

function testFindRelations()
{
	$skip_step = array(
		RelationConstant::STEP_SIX_DESCR,
		RelationConstant::STEP_NINE_DESCR,
	);

	// тест
	$entity = $this->getEntity(array(
		'body' => 'Подпункт 1.2 пункта 1 Приказа Федерального агентства специального ' .
			'строительства от 10 октября 2006 г. N 405 "О видах поощрений и ' .
			'награждений федеральных государственных гражданских служащих и ' .
			'военнослужащих Федерального агентства специального строительства" ' .
			'(зарегистрирован в Министерстве юстиции Российской Федерации 22 ' .
			'ноября 2006 г., регистрационный N 8515) изменить и изложить в ' .
			'следующей редакции:'
	));
	$matches = $this->findRelations($entity, $skip_step);
	$this->assertEquals(1, count($matches));
	$this->unserialize_match($matches);
	$this->unserialize_match($matches[0]['data']['submatches']);
	$this->assertEquals(4, count($matches[0]['data']['submatches']));
	$this->assertEquals(405, $matches[0]['data']['number'][0]['number']);
	$this->assertEquals(22, $matches[0]['data']['start']);
	$this->assertEquals(29, $matches[0]['data']['end']);
	$this->assertEquals(DocumentConstant::TYPE_ORDER, $matches[0]['data']['type_id']);

	// тест
	$entity = $this->getEntity(array(
		'body' => 'приказу МИД России и МНС России от 13.11.2000 N 13747/БГ-3-06/386 ' .
			'"Об освобождении от налогообложения реализации услуг по сдаче в ' .
			'аренду служебных и жилых помещений иностранным гражданам и ' .
			'организациям", или в список государств, в отношении которых ' .
			'применяется освобождение от налога на добавленную стоимость ' .
			'(далее - НДС) при сдаче в аренду служебных и жилых помещений ' .
			'иностранным гражданам и юридическим лицам (приложение к письму ' .
			'Госналогслужбы России от 13.07.94 N ЮУ-6-06/80н "Об освобождении ' .
			'от налога на добавленную стоимость сдачи в аренду служебных и ' .
			'жилых помещений иностранным гражданам и юридическим лицам, ' .
			'аккредитованным в Российской Федерации").'
	));
	$matches = $this->findRelations($entity, $skip_step);
	$this->assertEquals(2, count($matches));
	$this->unserialize_match($matches);
	$this->assertEquals(DocumentConstant::TYPE_ORDER,  $matches[0]['data']['type_id']);
	$this->assertEquals(35, $matches[0]['data']['start']);
	$this->assertEquals(65, $matches[0]['data']['end']);
	$this->assertEquals(DocumentConstant::TYPE_LETTER, $matches[1]['data']['type_id']);
	$this->assertEquals(458, $matches[1]['data']['start']);
	$this->assertEquals(480, $matches[1]['data']['end']);

	// тест
	$entity = $this->getEntity(array(
		'body' => 'пункты 2 - 4 статьи 1 Федерального закона от 17 декабря 2009 ' .
			'года N 313 ФЗ "О приостановлении действия отдельных положений ' .
			'некоторых законодательных актов Российской Федерации в связи ' .
			'с Федеральным законом "О федеральном бюджете на 2010 год и на ' .
			'плановый период 2011 и 2012 годов" (Собрание законодательства ' .
			'Российской Федерации, 2009, N 51, ст. 6150).'
	));
	$matches = $this->findRelations($entity, $skip_step);

	$matches_count = in_array(RelationConstant::STEP_TWELVE_DESCR, RelationsGrabberStepGluer::$links_in_link_steps)
		? 3 : 2;
	$this->assertEquals($matches_count, count($matches));
	$this->unserialize_match($matches);
	$this->unserialize_match($matches[0]['data']['submatches']);
	if ($matches_count == 3) {
		$this->unserialize_match($matches[2]['data']['submatches']);
	}
	$this->assertEquals(4, count($matches[0]['data']['submatches']));

	// тест
	$entity = $this->getEntity(array(
		'body' => 'В соответствии со статьей 17 Федерального конституционного ' .
			'закона от 31 декабря 1996 года N 1-ФКЗ "О судебной системе ' .
			'Российской Федерации", а также пунктом 2 статьи 13 УК РФ и ' .
			'статьей 14 Конституции Российской Федерации'
	));
	$matches = $this->findRelations($entity, $skip_step);
	$this->assertEquals(4, count($matches));
	$this->unserialize_match($matches);
	$this->assertEquals(DocumentConstant::TYPE_FEDERAL_CODEX_LAW,  $matches[0]['data']['type_id']);
	$this->assertEquals(DocumentConstant::TYPE_CODEX,  $matches[1]['data']['type_id']);
	$this->assertEquals(DocumentConstant::TYPE_CONSTITUTION,  $matches[2]['data']['type_id']);
}