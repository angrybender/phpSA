<?php

$this->_loaded = false;

$output = array();
exec('LANG=en_US.UTF-8 xls2csv -f %d.%m.%Y ' . $this->_file_name . ' 2>/dev/null', $output, $result);
// stderr перенаправляем в dev/null
// без LANG=en_US.UTF-8 могут быть казусы если у юзера, из под которого запущен пхп локаль не юникодовская

if ($result !== 0) {
	return false;
}

$table = array();
$fields_cnt = count($this->_fields);
$fields_names = array_keys($this->_fields);

foreach ($output as $line => $value) {
	$value = str_getcsv($value);
	$fields_csv_cnt = count($value);

	if (count(array_filter($value))>0) { // иногда утилита конвертации выдает полупустые завершающие строки и иногда - целые пустые массивы
		if ($fields_csv_cnt < $fields_cnt) { // тут происходит автодополнение конечных элементов массива - утилита конвертации пропускает пустые ячейки в конце строки
			$extended = array_fill(0, $fields_cnt-$fields_csv_cnt, '');
			$value = array_merge($value, $extended);
			unset($extended);
		}
		if ($fields_csv_cnt > $fields_cnt) { // иногда бывает и так
			$value = array_slice($value, 0, $fields_cnt);
		}

		$value = array_combine($fields_names, $value);
		$value[$this->_line_number_field] = $line+1;
		$table[] = $value;
	}
}

if ($this->_drop_header) {
	array_shift($table);
}

$this->_collection = $table;
$this->_loaded = true;


foreach ($this->_fields as $field_name => $field_type) {
	$params = array();

	if (empty($field_type)) {
		$field_type = 'string';
	}

	if (is_array($field_type)) {
		$params = $field_type;
		$field_type = 'enum';
	}

	$class_name = 'Active\Models\Excel\Validators\\' . ucfirst($field_type); // у этой модели свои особые упрощенные валидаторы
	if (!class_exists($class_name)) {
		$class_name = 'Active\Forms\Validators\\' . ucfirst($field_type); // ну или пользуем валидатор от форм
	}

	$this->_validators[$field_name] = new $class_name($params);

	$this->_fields[$field_name] = $field_type; // для порядка, чтобы далее единообразно ориентироваться на строковый код типа, а не пользовать всякие is_*()
}

if (is_numeric($date)) {
	$dateValue = intval($date);

	$my_excelBaseDate = 25569;
	//	Adjust for the spurious 29-Feb-1900 (Day 60)
	if ($dateValue < 60) {
		--$my_excelBaseDate;
	}

	// Perform conversion
	if ($dateValue >= 1) {
		$utcDays = $dateValue - $my_excelBaseDate;
		$returnValue = round($utcDays * 86400);
		if (($returnValue <= PHP_INT_MAX) && ($returnValue >= -PHP_INT_MAX)) {
			$returnValue = (integer) $returnValue;
		}
	} else {
		// при необходимости можно добавить обработку дробныъ значений - даты со временем в представлении экселя
		$hours = round($dateValue * 24);
		$mins = round($dateValue * 1440) - round($hours * 60);
		$secs = round($dateValue * 86400) - round($hours * 3600) - round($mins * 60);
		$returnValue = (integer) gmmktime($hours, $mins, $secs);
	}

	$date = date('d.m.Y', $returnValue);
}