<?php
/**
 *
 * @author pdepend
 */

private function collectMetrics()
{
	if ($this->coupling === null) {
		throw new RuntimeException('Missing Coupling analyzer.');
	}
	if ($this->cyclomaticComplexity === null) {
		throw new RuntimeException('Missing Cyclomatic Complexity analyzer.');
	}
	if ($this->inheritance === null) {
		throw new RuntimeException('Missing Inheritance analyzer.');
	}
	if ($this->nodeCount === null) {
		throw new RuntimeException('Missing Node Count analyzer.');
	}
	if ($this->nodeLoc === null) {
		throw new RuntimeException('Missing Node LOC analyzer.');
	}

	$coupling    = $this->coupling->getProjectMetrics();
	$cyclomatic  = $this->cyclomaticComplexity->getProjectMetrics();
	$inheritance = $this->inheritance->getProjectMetrics();
	$nodeCount   = $this->nodeCount->getProjectMetrics();
	$nodeLoc     = $this->nodeLoc->getProjectMetrics();

	return array(
		'cyclo'   =>  $cyclomatic['ccn2'],
		'loc'     =>  $nodeLoc['eloc'],
		'nom'     =>  ($nodeCount['nom'] + $nodeCount['nof']),
		'noc'     =>  $nodeCount['noc'],
		'nop'     =>  $nodeCount['nop'],
		'ahh'     =>  round($inheritance['ahh'], 3),
		'andc'    =>  round($inheritance['andc'], 3),
		'fanout'  =>  $coupling['fanout'],
		'calls'   =>  $coupling['calls']
	);
}