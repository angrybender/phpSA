<?php

abstract class MyClass {

	public function func1()
	{

	}

	private function pfunc($a = 1, array $b_arrr){
		return $a + count($b_arrr);
	}

	abstract public function bf();
	abstract public function doSimpleGetRequest($url, array $get_params, array $request_headers = array());

	private $b = 1;
}