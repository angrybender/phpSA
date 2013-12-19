<?php

include 'bootstrap.php';

//$cs = file_get_contents('source/sub_extract.php');
$cs = file_get_contents('source/subsimple.php');

//$results = Procedures::get_all_procedures_in_code($cs);

//print_r($results);
print_r(Tokenizer::get_tokens($cs));

echo PHP_EOL;

