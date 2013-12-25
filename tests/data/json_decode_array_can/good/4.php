<?php
/**
 *
 * @author k.vagin
 */

function listen() {
	if(Insight_Util::getRequestHeader('x-insight')!='transport') {
		return false;
	}

	$payload = $_POST['payload'];
	if(get_magic_quotes_gpc()) {
		$payload = stripslashes($payload);
	}
	$payload = Insight_Util::json_decode($payload);
	$file = $this->getPath($payload['key']);
	if(file_exists($file)) {
		readfile($file);

		// delete old files
		// TODO: Only do this periodically

		$time = time();
		foreach (new DirectoryIterator($this->getBasePath()) as $fileInfo) {
			if($fileInfo->isDot()) continue;
			if($fileInfo->getMTime() < $time-self::TTL) {
				unlink($fileInfo->getPathname());
			}
		}
	}
	return true;
}