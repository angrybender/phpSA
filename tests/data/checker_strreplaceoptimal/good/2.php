<?php
/**
 *
 * @author k.vagin
 */

$class_name = "Module_" . str_replace(":", "__", $module_name);
$module_file = Config::get('host.local.modules') . str_replace(":", DS . "mod.", $module_name) . '.php';
$module_tpl  = 'modules' . DS . str_replace(":", DS . "mod.", $module_name).'.tpl';