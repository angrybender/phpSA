<?php
/**
 *
 * @author k.vagin
 */

if (!isset($arguments['N'])) {
	throw new Exception("Need option N, name of checker");
}

$class_name = $arguments['N'];
$class_file_name = __DIR__ . '/../../checkers/' . $arguments['N'] . '.php';

if (file_exists($class_file_name)) {
	throw new Exception("Class exist");
}

// сохранение класса
$class_code = file_get_contents(__DIR__ . '/../templates/Checkers.tpl');
$class_code = str_replace(array(
	'%NAME%'
),
array(
	$class_name
), $class_code);

file_put_contents($class_file_name, $class_code);

// юнит тесты:
function from_camel_case($input) {
	preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
	$ret = $matches[0];
	foreach ($ret as &$match) {
		$match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
	}
	return implode('_', $ret);
}

$utest_data_path = strtolower('checker_' . from_camel_case($arguments['N']));
$utest_file_name = __DIR__ . '/../../tests/suites/Checker_' . $arguments['N'] . '__Test.php';

$class_code = file_get_contents(__DIR__ . '/../templates/Checkers_utest.tpl');
$class_code = str_replace(array(
		'%NAME%',
		'%DATA%'
	),
	array(
		$class_name,
		$utest_data_path
	), $class_code);

@mkdir(__DIR__ . '/../../tests/data/' . $utest_data_path);
@mkdir(__DIR__ . '/../../tests/data/' . $utest_data_path . '/good');
@mkdir(__DIR__ . '/../../tests/data/' . $utest_data_path . '/bad');
file_put_contents($utest_file_name, $class_code);