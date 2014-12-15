<?php
/**
 *
 * @author k.vagin
 */

namespace Core\Flow\Trace;


class Scope
{
	/**
	 * @var Variable[]
	 */
	private $scope = array();

	/**
	 * @return array
	 */
	public function getScope()
	{
		return $this->scope;
	}

	/**
	 * @param string|\PHPParser_Node $name
	 * @return Variable|null
	 */
	public function getVariable($name)
	{
		if ($name instanceof \PHPParser_Node) {
			$name = \Core\Tokenizer::cleanPrinter($name);
		}

		if (isset($this->scope[$name])) {
			return $this->scope[$name];
		}
		else {
			return null;
		}
	}

	public function addVariable(Variable $var)
	{
		$name = $var->getName();
		if (isset($this->scope[$name])) {
			$this->mergeVar($var);
		}
		else {
			$this->scope[$name] = $var;
		}
	}

	public function setVariable(Variable $var)
	{
		$name = $var->getName();
		$this->scope[$name] = $var;
	}

	private function mergeVar(Variable $variable)
	{
		$name = $variable->getName();
		$variable->merge($this->scope[$name]);
		$this->scope[$name] = $variable;
	}

	/**
	 * добавить в текущий скоуп данные из переданного (переменные перезаписываются)
	 * @param Scope $scope
	 */
	public function merge(Scope $scope)
	{
		$this->scope = $scope->getScope();
	}
}