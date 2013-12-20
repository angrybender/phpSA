<?php

include 'bootstrap.php';


//print_r(Tokenizer::get_tokens_of_expression('$a . $b'));
//die();

$cs = file_get_contents('/home/kirill/apps/sa/tests/data/checker_date_bad_operation/bad/3');

$dec = new DateBadOperation();

var_dump($dec->check($cs));

echo PHP_EOL;

