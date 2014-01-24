<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';
include __DIR__ . '/../../workers/ClassInformation.php';
include __DIR__ . '/../../hooks/IndexerClassInformation.php';

class HooksIndexerClassInformation extends PHPUnit_Framework_TestCase
{
	public function test_index_by_method_name_method_args_count()
	{
		// подсовываем в воркер:
		$worker = new \Workers\ClassInformation();
		$worker->class_info = array(array(
			'name' => 'test_class1',
			'methods' => array(
				array(
					'name' => 'run',
					'args' => array()
				),
				array(
					'name' => 'stop',
					'args' => array(
						'BY_LINK', 'BY_VAL'
					)
				),
			),
			'properties' => array()),
			array(
			'name' => 'test_class2',
			'methods' => array(
				array(
					'name' => 'Run',
					'args' => array()
				),
				array(
					'name' => 'stop',
					'args' => array(
						'BY_LINK'
					)
				),
			),
			'properties' => array()
		));

		$hook = new \Hooks\IndexerClassInformation();
		$hook->run();

		$this->assertEquals($hook->find_all_methods('run'), array(
			array(
				'name' => 'run',
				'args' => array()
			),
			array(
				'name' => 'run',
				'args' => array()
			),
		));

		$this->assertEquals($hook->find_all_methods('run', false), array(
			array(
				'name' => 'run',
				'args' => array()
			),
			array(
				'name' => 'run',
				'args' => array()
			),
		));

		$this->assertEquals($hook->find_all_methods('stop', 1), array(
			array(
				'name' => 'stop',
				'args' => array('BY_LINK')
			)
		));

		$this->assertEquals($hook->find_all_methods('stop', 2), array(
			array(
				'name' => 'stop',
				'args' => array('BY_LINK', 'BY_VAL')
			)
		));
	}
} 