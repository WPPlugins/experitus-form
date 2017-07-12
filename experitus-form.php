<?php
/*
Plugin Name: Experitus form
Plugin URI:  
Description: This plugins integrates your WP site and Experitus account by installing an orders form
Version:     0.4
Author:      Experitus
*/

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
	exit;
}

/**
 * Plugin main folder and file
 */
define( 'EXPERITUS_ROOT_FOLDER', plugin_dir_path( __FILE__ ) );
define( 'EXPERITUS_ROOT_FILE', __FILE__ );
define( 'EXPERITUS_URL',  'https://app.experitus.io/');

/**
 * Adds options to database
 */
function add_experitus_options() {
	add_option( 'experitus_request_attributes' );
	add_option( 'experitus_connection_data' );
	add_option( 'experitus_request_items' );
	add_option( 'experitus_captcha_data' );
	add_option( 'experitus_ssl_verifypeer' );
	add_option( 'experitus_payments_data' );
	add_option( 'experitus_options_check' );
	add_option( 'experitus_block_dates' );
	add_option( 'experitus_countries' );
}
register_activation_hook( __FILE__, 'add_experitus_options' );

/**
 * Removes options from database
 */
function remove_experitus_options() {
	delete_option( 'experitus_request_attributes' );
	delete_option( 'experitus_connection_data' );
	delete_option( 'experitus_request_items' );
	delete_option( 'experitus_captcha_data' );
	delete_option( 'experitus_ssl_verifypeer' );
	delete_option( 'experitus_payments_data' );
	delete_option( 'experitus_options_check' );
	delete_option( 'experitus_block_dates' );
	delete_option( 'experitus_countries' );
}
register_uninstall_hook( __FILE__, 'remove_experitus_options' );

if (is_admin()) {
	require_once( 'includes/controllers/experitus-admin-controller.php' );
	$experitus_admin_controller = new experitusAdminController;
}
else {
	require_once( 'includes/controllers/experitus-form-controller.php' );
	$experitus_form_controller = new experitusFormController;
}

