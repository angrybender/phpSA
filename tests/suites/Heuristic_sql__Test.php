<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';

class Heuristic_is_maybe_sql_query extends PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider provider
	 */
	public function test($query, $is)
	{
		$this->assertEquals($is, \Heuristic::is_maybe_sql_query($query));
	}

	public function provider()
	{
		return array(
			array(
				'select * from `table`',
				true
			),
			array(
				"ALTER TABLE `my_sql_table` ADD `middle_name`
  					VARCHAR( 50 ) NOT NULL AFTER `surname`",
				true
			),
			array(
				"DELETE
					FROM
						`my_sql_table`
				where
					surname='Сидоров'",
				true
			),
			array(
				"SELECT COUNT(*) FROM `my_sql_table` where surname like 'П%'",
				true
			),
		);
	}
} 