<?php
/**
 *
 * @author k.vagin
 */

function testUntypographBody()
{
	$body = 'Открытое страховое акционерного общества "Ингосстрах" в лице филиала ОСАО "Ингосстрах"';
	$expected_string = 'Открытое страховое акционерного общества "Ингосстрах" в лице филиала ОСАО "Ингосстрах"';
	$string = RelationsGrabberStepBase::untypographBody($body);
	$this->assertEquals($expected_string, $string);


	$body = 'Открытое страховое акционерного&thinsp;общества «Ингосстрах» в лице филиала ОСАО &quot;Ингосстрах&quot;';
	$expected_string = 'Открытое страховое акционерного        общества "Ингосстрах" в лице филиала ОСАО   "   Ингосстрах  "   ';
	$string = RelationsGrabberStepBase::untypographBody($body);
	$this->assertEquals($expected_string, $string);


	$body = 'Открытое &mdash; страховое &ndash; акционерного&thinsp;общества&minus;«Ингосстрах»&laquo;в&raquo;лице&ldquo;филиала&rdquo;ОСАО„&quot;Ингосстрах&quot;“';
	$expected_string = 'Открытое    -    страховое    -    акционерного        общества   -   "Ингосстрах"      "в"      лице      "филиала"      ОСАО"  "   Ингосстрах  "   "';
	$string = RelationsGrabberStepBase::untypographBody($body);
	$this->assertEquals($expected_string, $string);
}