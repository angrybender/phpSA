<?php
/**
 *
 * @author k.vagin
 */

function wpmu_signup_user( $user, $user_email, $meta = array() ) {
	global $wpdb;

	// Format data
	$user = preg_replace( '/\s+/', '', sanitize_user( $user, true ) );
	$user_email = sanitize_email( $user_email );
	$key = substr( md5( time() . rand() . $user_email ), 0, 16 );
	$meta = serialize($meta);

	$wpdb->insert( $wpdb->signups, array(
		'domain' => '',
		'path' => '',
		'title' => '',
		'user_login' => $user,
		'user_email' => $user_email,
		'registered' => current_time('mysql', true),
		'activation_key' => $key,
		'meta' => $meta
	) );

	wpmu_signup_user_notification($user, $user_email, $key, $meta);
}