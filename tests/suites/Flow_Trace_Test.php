<?php
/**
 *
 * @author k.vagin
 */

include __DIR__ . '/../../bootstrap.php';

class Flow_Trace_Test extends PHPUnit_Framework_TestCase
{
	private $path = '/../data/flow_trace/';

	public function testOperators()
	{
		$nodes = \Core\Tokenizer::parse_file(__DIR__ . $this->path . 'blocks.php');

		$tracer = new \Core\Flow\Trace\Tracer($nodes);
		$tracer->trace();

		//print_r($tracer->getScope());
	}
}