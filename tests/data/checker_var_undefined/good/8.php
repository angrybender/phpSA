<?php
/**
 *
 * @author k.vagin
 */
/**
 *  Multi-call Function:  Processes method
 *
 * @access	public
 * @param	mixed
 * @return	object
 */
function do_multicall($call)
{
	if ($call->kindOf() != 'struct')
	{
		return $this->multicall_error('notstruct');
	}
	elseif ( ! $methName = $call->me['struct']['methodName'])
	{
		return $this->multicall_error('nomethod');
	}

	list($scalar_type,$scalar_value)=each($methName->me);
	$scalar_type = $scalar_type == $this->xmlrpcI4 ? $this->xmlrpcInt : $scalar_type;

	if ($methName->kindOf() != 'scalar' OR $scalar_type != 'string')
	{
		return $this->multicall_error('notstring');
	}
	elseif ($scalar_value == 'system.multicall')
	{
		return $this->multicall_error('recursion');
	}
	elseif ( ! $params = $call->me['struct']['params'])
	{
		return $this->multicall_error('noparams');
	}
	elseif ($params->kindOf() != 'array')
	{
		return $this->multicall_error('notarray');
	}

	list($a,$b)=each($params->me);
	$numParams = count($b);

	$msg = new XML_RPC_Message($scalar_value);
	for ($i = 0; $i < $numParams; $i++)
	{
		$msg->params[] = $params->me['array'][$i];
	}

	$result = $this->_execute($msg);

	if ($result->faultCode() != 0)
	{
		return $this->multicall_error($result);
	}

	return new XML_RPC_Values(array($result->value()), 'array');
}
