<?php
/**
 *
 * @author k.vagin
 */

function wp_register_sidebar_widget($id, $name, $output_callback, $options = array()) {
	global $wp_registered_widgets, $wp_registered_widget_controls, $wp_registered_widget_updates, $_wp_deprecated_widgets_callbacks;

	$id = strtolower($id);

	if ( empty($output_callback) ) {
		unset($wp_registered_widgets[$id]);
		return;
	}

	$id_base = _get_widget_id_base($id);
	if ( in_array($output_callback, $_wp_deprecated_widgets_callbacks, true) && !is_callable($output_callback) ) {
		if ( isset($wp_registered_widget_controls[$id]) )
			unset($wp_registered_widget_controls[$id]);

		if ( isset($wp_registered_widget_updates[$id_base]) )
			unset($wp_registered_widget_updates[$id_base]);

		return;
	}

	$defaults = array('classname' => $output_callback);
	$options = wp_parse_args($options, $defaults);
	$widget = array(
		'name' => $name,
		'id' => $id,
		'callback' => $output_callback,
		'params' => array_slice(func_get_args(), 4)
	);
	$widget = array_merge($widget, $options);

	if ( is_callable($output_callback) && ( !isset($wp_registered_widgets[$id]) || did_action( 'widgets_init' ) ) ) {
		do_action( 'wp_register_sidebar_widget', $widget );
		$wp_registered_widgets[$id] = $widget;
	}
}