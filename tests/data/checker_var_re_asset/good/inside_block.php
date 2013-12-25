<?php
/**
 *
 * @author k.vagin
 */

function _updater($container = 'this', $controller, $options = '')
{
	// ajaxStart and ajaxStop are better choices here... but this is a stop gap
	if ($this->CI->config->item('javascript_ajax_img') == '')
	{
		$loading_notifier = "Loading...";
	}
	else
	{
		$loading_notifier = '<img src=\'' . $this->CI->config->slash_item('base_url') . $this->CI->config->item('javascript_ajax_img') . '\' alt=\'Loading\' />';
	}

	return $loading_notifier;
}