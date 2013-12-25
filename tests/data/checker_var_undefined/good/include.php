<?php
/**
 *
 * @author k.vagin
 */

private function _load_agent_file()
{
	if (defined('ENVIRONMENT') AND is_file(APPPATH.'config/'.ENVIRONMENT.'/user_agents.php'))
	{
		include(APPPATH.'config/'.ENVIRONMENT.'/user_agents.php');
	}
	elseif (is_file(APPPATH.'config/user_agents.php'))
	{
		include(APPPATH.'config/user_agents.php');
	}
	else
	{
		return FALSE;
	}

	$return = FALSE;

	if (isset($platforms))
	{
		$this->platforms = $platforms;
		unset($platforms);
		$return = TRUE;
	}

	if (isset($browsers))
	{
		$this->browsers = $browsers;
		unset($browsers);
		$return = TRUE;
	}

	if (isset($mobiles))
	{
		$this->mobiles = $mobiles;
		unset($mobiles);
		$return = TRUE;
	}

	if (isset($robots))
	{
		$this->robots = $robots;
		unset($robots);
		$return = TRUE;
	}

	return $return;
}
