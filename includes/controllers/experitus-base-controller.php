<?php

/**
 * Plugin base controller
 *
 * @package Experitus Orders Page
 * @since 0.1
 */

class experitusBaseController {
	
	/**
	 * Holds plugin notifications
	 */
	public $notifications = array();
	
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	public $options = array();
	
	/**
	 * Class constructor
	 */
	public function __construct() {
		add_shortcode( 'experitus_orders_form', array($this, 'handle_shortcode') );
	}
	
	protected function populateOptions() {
		$this->options['request_attributes'] = get_option('experitus_request_attributes');
		$this->options['connection_data'] = get_option( 'experitus_connection_data' );
		$this->options['request_items'] = get_option( 'experitus_request_items' );
		$this->options['captcha_data'] = get_option( 'experitus_captcha_data' );
		$this->options['ssl_verifypeer'] = get_option( 'experitus_ssl_verifypeer' );
		$this->options['payments_data'] = get_option( 'experitus_payments_data' );
		$this->options['options_check'] = get_option( 'experitus_options_check' );
		$this->options['block_dates'] = get_option( 'experitus_block_dates' );
		$this->options['countries'] = get_option( 'experitus_countries' );
	}
	
	
	/** 
	 * Adds a notification
	 */
	public function add_notification( $type, $text ) {
		$this->notifications[] = array('type' => $type, 'text' => $text);
	}
	
	
	/** 
	 * Generates and returns api action url
	 */
	protected function get_api_url( $action, $company_alias = null ) {
		if ( !$company_alias )
			$company_alias = $this->options['connection_data']['company_alias'];
		return EXPERITUS_URL . 'en/' . $company_alias . '/api/' . $action . '/';
	}
	
	/** 
	 * Performs API request
	 */
	 protected function make_api_request( $action, $method = 'GET', $company_alias = null, array $params = array(), array $args = array() ) {
		$api_url = $this->get_api_url( $action, $company_alias );
		if ( !isset($args['sslverify']) || !$args['sslverify'] )
			$args['sslverify'] = (bool) $this->options['ssl_verifypeer'];
		$args['timeout'] = 10;
		if ( !isset($params['api_key']) || !$params['api_key'] )
			$params['api_key'] = $this->options['connection_data']['api_key'];
		if (strtoupper($method) == 'GET') {
			$url =  $api_url . '?' . http_build_query( $params );
			return wp_remote_get( $url, $args );
		}
		elseif (strtoupper($method) == 'POST') {
			return wp_remote_post( $api_url, array_merge( array('body' => $params), $args ) );
		}
	}
	
	
	/** 
	 * Checks if Google reCAPTCHA is enabled for plugin
	 */
	public function if_captcha_enabled() {
		return isset( $this->options['captcha_data']['enable_captcha'] ) && $this->options['captcha_data']['enable_captcha'];
	}
	
	
	/** 
	 * Renders view file
	 */
	protected function render($view_name) {
		include EXPERITUS_ROOT_FOLDER . 'includes/views/'.$view_name.'.php';
	}
}