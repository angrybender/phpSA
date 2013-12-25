phpSA
=====
include 'bootstrap.php';

//print_r(Tokenizer::get_tokens('<?php case ($A);'));
//die(PHP_EOL);

$suite = new \Analisator\Suite();
$suite->set_project_path('/pdfgen/');


$suite->start();


echo PHP_EOL;
