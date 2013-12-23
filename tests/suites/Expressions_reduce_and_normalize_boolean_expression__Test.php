<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';

class Expressions_reduce_and_normalize_boolean_expression extends PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider provider
	 */
	public function test_rn($expression, $result)
	{
		$this->assertEquals($result, \Expressions::reduce_and_normalize_boolean_expression($expression));
	}

	public function provider()
	{
		return array(
			array(
				'($str!=\'\'&&$this->config->item(\'permitted_uri_chars\')!=\'\'&&$this->config->item(\'enable_query_strings\')==FALSE)',
				'($rvar_11&&$rvar_14&&$rvar_15)'
			),
			array(
				'($uri=="/"||empty($uri))',
				'($rvar_3||$rvar_2)'
			),
			array(
				'($year or $day)',
				'($rvar_1 or $rvar_2)'
			),
			array(
				'($year%400==0OR($year%4==0AND$year%100!=0))',
				'($rvar_3 OR ($rvar_5 AND $rvar_7))'
			),
			array(
				'(isset($rowDimensions[$row])and!$rowDimensions[$row]->getVisible())',
				'($rvar_2 and !$rvar_5)'
			),
			array(
				'(($segMatcher=!$segMatcher)&&(preg_match(\'/(^|\])[^\[]*[\'.self::$possibleDateFormatCharacters.\']/i\',$subVal)))',
				'($rvar_6&&$rvar_5)'
			),
			array(
				'((!isset($p_options[PCLZIP_OPT_TEMP_FILE_OFF]))&&(isset($p_options[PCLZIP_OPT_TEMP_FILE_ON])||(isset($p_options[PCLZIP_OPT_TEMP_FILE_THRESHOLD])&&($p_options[PCLZIP_OPT_TEMP_FILE_THRESHOLD]<=$p_header[\'size\']))))',
				'((!$rvar_5)&&($rvar_6||($rvar_7&&$rvar_10)))'
			),
			array(
				'(preg_match(\'/<?xml.*encoding=[\\\'"](.*?)[\\\'"].*?>/um\',$data,$matches))',
				'$rvar_3'
			),
			// вот по сюда - из за PHPExcel

			array(
				'($data==\DataBase::EMPTY_DATE||$data==\DataBase::EMPTY_DATETIME)',
				'($rvar_8||$rvar_9)'
			),
			array(
				'($this->{\'_\'.$resource}>$value)',
				'($rvar_4>$rvar_2)'
			),
			array(
				'($number >= 1024 && $number <= 1048575) || ($number <= - 1024 && $number > - 1048575)',
				'($rvar_4&&$rvar_5)||($rvar_6&&$rvar_7)'
			),

			array(
				'( ($url = $style->background_image) && $url !== "none"
         && ($repeat = $style->background_repeat) && $repeat !== "repeat" &&  $repeat !== "repeat-y"
       )',
				''
			)
		);
	}
} 