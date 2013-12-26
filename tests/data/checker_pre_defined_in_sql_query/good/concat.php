<?php
// $Id: api.php 1735 2011-06-08 07:53:47Z denis_chernosov $
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_DIR', realpath(dirname(__FILE__) . DS . '..' . DS) . DS);
define('KERNEL_DIR', ROOT_DIR . 'kernel' . DS);
@ini_set('memory_limit', '128M');
require_once (KERNEL_DIR . 'inc.start.php');
if (Config::get('host.debug_mode')) {
	$body = file_get_contents('php://input');
	Logger::setLogFile('/tmp/api_log_fla.txt' . $_REQUEST['module'] . '_' . $_REQUEST['action']);
	Logger::log('Raw post data - ' . $body);
	Logger::log('Request:' . var_export($_REQUEST, true));
}
$is_post = ($_SERVER['REQUEST_METHOD'] == 'POST');
$request_data = $is_post ? $_POST : $_GET;
if (Config::get('settings.pinba')) {
	$pinba_tags = array(
		'project' => 'fla_fla'
	, 'module' => 'fla_fla_' . $request_data['module']
	, 'action' => $request_data['action']
	);
	pinba_timer_start($pinba_tags);
}
if ($is_post) {
	if (empty($request_data['action']) && empty($request_data['module']) && !empty($_GET['action'])
		&& !empty($_GET['module'])) {
		$request_data['action'] = $_GET['action'];
		$request_data['module'] = $_GET['module'];
	}
	if (empty($request_data['format']) && !empty($_GET['format'])) {
		$request_data['format'] = $_GET['format'];
	}
}
if (function_exists('pinba_script_name_set')) {
	pinba_script_name_set('flag/api_' . $request_data['module'] . '/' . $request_data['action']);
}
$processor = new ApiMain($request_data, $is_post);
$processor->execute();
if (Config::get('settings.pinba')) {
	pinba_timer_stop($pinba_tags);
}
