<?php
/**
 *
 * @author k.vagin
 */
include 'Utils.php';
include 'Tokenizer.php';
include 'Expressions.php';
include 'Variables.php';
include 'checkers/Conditions.php';

$source = file_get_contents('stat_emxpl.php');
$tokens = Tokenizer::get_tokens($source);

//print_r(get_all_ifconditions($tokens));

$expression = Expressions::reduce_and_normalize_boolean_expression('$is_in_billing && $is_in_billing && !$is_local_blocked && !$is_billing_blocked'); // $a >> 1 - $a++ +1/($d-date($a+1)) && $this->start() || !$a
if (Conditions::check_boolean_expression($expression)) {
	echo 'Expression OK';
}
else {
	echo 'Maybe error';
}

//var_dump($expression);

//print_r(get_all_variables(8));

die(PHP_EOL);
$tokens = Tokenizer::get_tokens('<? $a = TRUE;');
foreach ($tokens as $token) {

	if (is_array($token)) {
		$name = $token[0];
		$value = trim($token[1]);
	}
	else {
		$name = 'lexem';
		$value = $token;
	}

	echo $name . ' : ' . $value . PHP_EOL;
}