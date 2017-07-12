<?php

require_once('experitus-base-controller.php');

/**
 * Plugin backend controller
 *
 * @package Experitus Orders Page
 * @since 0.1
 */

class experitusAdminController extends experitusBaseController {
	
	/**
	 * Holds current tab on plugin admin page
	 */
	public $current_tab;
	
	/**
	 * Class constructor
	 */
	public function __construct() {
		parent::__construct();
		
		add_action( 'admin_menu', array($this, 'add_form_to_menu') );
		add_action( 'admin_init', array($this, 'register_settings') );
		add_action( 'admin_notices', array($this, 'print_errors') );
	}
	
	/**
	 * Add tools menu item
	 */
	public function add_form_to_menu() {
		if ( !current_user_can( 'manage_options' ) )
			return;
		add_management_page(
			__( 'Experitus Form' ),
			__( 'Experitus Form' ),
			'manage_options',
			'experitus-form',
			array($this, 'action_connect')
		);
	}
	
	/**
	 * Register settings
	 */
	public function register_settings() {
		$this->register_contact_settings();
		$this->register_captcha_settings();
	}
	
	
	/**
	 * Register settings for cotact section
	 */
	private function register_contact_settings() {
		register_setting(
			'experitus_connection_group', // Option group
			'experitus_connection_data', // Option name
			array( $this, 'handleConnectData' )
		);

		add_settings_section(
			'experitus_connect_section', // ID
			__( 'Connect to Experitus account' ), // Title
			array( $this, 'print_connect_info' ), // Callback
			'experitus_connection_group' // Page
		); 

		add_settings_field(
			'company_alias', // ID
			__( 'Company Alias' ), // Title 
			array( $this, 'company_alias_callback' ), // Callback
			'experitus_connection_group', // Page
			'experitus_connect_section' // Section
		);  

		add_settings_field(
			'api_key', // ID
			__( 'API Key' ), // Title 
			array( $this, 'api_key_callback' ), // Callback
			'experitus_connection_group', // Page
			'experitus_connect_section' // Section
		);
	}
	
	
	/**
	 * Register settings for captcha section
	 */
	private function register_captcha_settings() {
		register_setting(
			'experitus_captcha_group', // Option group
			'experitus_captcha_data', // Option name
			array( $this, 'handleCaptchaData' )
		);

		add_settings_section(
			'experitus_captcha_section', // ID
			__( 'Google reCAPTCHA settings' ), // Title
			array( $this, 'print_captcha_info' ), // Callback
			'experitus_captcha_group' // Page
		);

		add_settings_field(
			'disable_recaptcha', // ID
			__( 'Captcha' ), // Title 
			array( $this, 'enable_captcha_callback' ), // Callback
			'experitus_captcha_group', // Page
			'experitus_captcha_section' // Section
		);  

		add_settings_field(
			'site_key', // ID
			__( 'Site Key' ), // Title 
			array( $this, 'site_key_callback' ), // Callback
			'experitus_captcha_group', // Page
			'experitus_captcha_section' // Section
		);  

		add_settings_field(
			'secret_key', // ID
			__( 'Secret Key' ), // Title 
			array( $this, 'secret_key_callback' ), // Callback
			'experitus_captcha_group', // Page
			'experitus_captcha_section' // Section
		);
	}
	
	/**
	 * Tries to connect with Experitus and creates messages
	 * 
	 * @param array $input Contains all settings fields as array keys
	 */
	public function handleConnectData($input) {
		if ( !current_user_can( 'manage_options' ) )
			return false;
		$error_messages = array();
		if(!$input['company_alias']) {
			$error_messages[] = __( 'Company alias cannot be empty.' );
		}
		if (!$input['api_key']) {
			$error_messages[] = __( 'API key cannot be empty.' );
		}
		if (count($error_messages) == 0) {
			$server_response = $this->make_api_request( 'request/get-form-data', 'GET', $input['company_alias'], array('api_key' => $input['api_key']), array('sslverify' => true) );
			
			if (is_wp_error($server_response)) {
				$server_response = $this->make_api_request( 'request/get-form-data', 'GET', $input['company_alias'], array('api_key' => $input['api_key']), array('sslverify' => false) );
				if (is_wp_error($server_response)) {
					$error_messages[] = $server_response->get_error_message();
				}
				else {
					update_option( 'experitus_ssl_verifypeer', false );
				}
			}
			else {
				update_option( 'experitus_ssl_verifypeer', true );
			}
			
			if (count($error_messages) == 0) {
				$response = json_decode($server_response['body'], true);
				if ($response['result'] != 'success') {
					$error_messages[] = isset($response['errors']) ? implode(' ', $response['errors']) : 'Could not connect to your Experitus account. Please check your credentials.';
				}
				else {
					$this->save_request_data($response);
					
					add_settings_error(
						'experitus_connection_data',
						esc_attr( 'settings_updated' ),
						__( 'You successfully connected to your Experitus account.' ),
						'notice-success'
					);
				}
			}
		}
		if (count($error_messages) > 0) {
			add_settings_error(
				'experitus_connection_data',
				esc_attr( 'settings_not_updated' ),
				implode( ' ', $error_messages ),
				'error'
			);
			return $this->options['connection_data'];
		}
		return $input;
	}
	
	
	/**
	 * Saves request form settings
	 */
	private function save_request_data($response) {
		if ( !current_user_can( 'manage_options' ) )
			return;
		update_option( 'experitus_request_attributes', $response['attributes'] );
		update_option( 'experitus_request_items', $response['items'] );
		update_option( 'experitus_payments_data', $response['payments_data'] );
		update_option( 'experitus_block_dates', $response['block_dates'] );
		update_option( 'experitus_countries', $response['countries'] );
		update_option( 'experitus_options_check', ['outdated' => false, 'time' => time()] );
	}
	
	/**
	 * Sanitizes input of captcha settings
	 * 
	 * @param array $input Contains all settings fields as array keys
	 */
	public function handleCaptchaData($input) {
		if ( !$input || !isset( $input['enable_captcha'] ) || !$input['enable_captcha'] ) {
			return array(
				'enable_captcha' => 0,
				'site_key' => $this->options['captcha_data']['site_key'],
				'secret_key' => $this->options['captcha_data']['secret_key']
			);
		}
		$error_messages = array();
		if(!$input['site_key']) {
			$error_messages[] = __( 'Site key field cannot be empty.' );
		}
		if (!$input['secret_key']) {
			$error_messages[] = __( 'API key cannot be empty.' );
		}
		if (count($error_messages) == 0) {
			foreach ($input as $field => $data) {
				$input[$field] = sanitize_text_field( $data );
			}
			add_settings_error(
				'experitus_captcha_data',
				esc_attr( 'settings_updated' ),
				__( 'Google reCaptcha credentials added to your form.' ),
				'notice-success'
			);
			return $input;
		}
		else {
			add_settings_error(
				'experitus_captcha_data',
				esc_attr( 'settings_not_updated' ),
				implode( ' ', $error_messages ),
				'error'
			);
			return $this->options['captcha_data'];
		}
	}
	
	/** 
	 * Prints settings errors
	 */
	public function print_errors() {
		settings_errors( 'experitus_connection_data' );
		settings_errors( 'experitus_captcha_data' );
	}
	
	/** 
	 * Print the Connect Section text
	 */
	public function print_connect_info() {
		print __( 'Enter credentials from your company\'s settnigs section of Experitus account.' );
	}
	
	/** 
	 * Print the Captcha Section text
	 */
	public function print_captcha_info() {
		print __( 'Google ReCAPTCHA is necessary to prevent automatic orders submitting by hackers. It is highly recommended to enable and enter credentials which could be found on <a href="https://www.google.com/recaptcha/admin" target="_blank">https://www.google.com/recaptcha/admin</a> page' );
	}
	
	/** 
	 * Get the company_alias option array and print one of its values
	 */
	public function company_alias_callback() {
		printf(
			'<input type="text" id="experitus_company_alias" name="experitus_connection_data[company_alias]" value="%s" />',
			isset( $this->options['connection_data']['company_alias'] ) ? esc_attr( $this->options['connection_data']['company_alias']) : ''
		);
	}

	/** 
	 * Get the api_key option array and print one of its values
	 */
	public function api_key_callback() {
		printf(
			'<input type="text" id="experitus_api_key" name="experitus_connection_data[api_key]" value="%s" />',
			isset( $this->options['connection_data']['api_key'] ) ? esc_attr( $this->options['connection_data']['api_key']) : ''
		);
	}
	
	/** 
	 * Get the site_key option array and print one of its values
	 */
	public function enable_captcha_callback() {
		printf(
			'<label for="experitus_enable_captcha"><input type="checkbox" id="experitus_enable_captcha" name="experitus_captcha_data[enable_captcha]" value="1" %s /> ' . __( 'Enable Google reCAPTCHA validation.' ) . '</label>',
			isset( $this->options['captcha_data']['enable_captcha'] ) && $this->options['captcha_data']['enable_captcha'] ? 'checked="checked"' : ''
		);
	}
	
	/** 
	 * Get the site_key option array and print one of its values
	 */
	public function site_key_callback() {
		printf(
			'<input type="text" id="experitus_site_key" class="experitus_captcha_credentials" name="experitus_captcha_data[site_key]" value="%s" />',
			isset( $this->options['captcha_data']['site_key'] ) ? esc_attr( $this->options['captcha_data']['site_key']) : ''
		);
	}
	
	/** 
	 * Get the secret_key option array and print one of its values
	 */
	public function secret_key_callback() {
		printf(
			'<input type="text" id="experitus_secret_key" class="experitus_captcha_credentials" name="experitus_captcha_data[secret_key]" value="%s" />',
			isset( $this->options['captcha_data']['secret_key'] ) ? esc_attr( $this->options['captcha_data']['secret_key']) : ''
		);
	}
	
	/** 
	 * Reloads request form settings
	 */
	private function reload_form_attributes() {
		if ( !$this->options['connection_data'] ) {
			$this->add_notification( 'error', __( 'Please connect to your Experitus account first!' ) );
			return false;
		}
		$server_response = $this->make_api_request( 'request/get-form-data' );
		if (is_wp_error($server_response)) {
			$this->add_notification( 'error', $server_response->get_error_message() );
			return;
		}
		
		$response = json_decode($server_response['body'], true);
		if ($response['result'] != 'success') {
			if ( isset( $response['errors'] ) )
				$this->add_notification( 'error', implode( ' ', $response['errors'] ) );
			else
				$this->add_notification( 'error', __( 'Could not connect to your Experitus account. Please check your credentials.' ) );
		}
		else {
			$this->save_request_data($response);
			$this->add_notification( 'success', __( 'You successfully reloaded Order Form Fields' ) );
		}
	}
	
	/** 
	 * Checks if booking form attributes are outdated
	 */
	private function if_options_outdated() {
		if ( !$this->options['connection_data'] || !$this->options['request_attributes'] )
			return false;
		if ( isset( $this->options['options_check']['outdated'] ) && $this->options['options_check']['outdated'] == true )
			return true;
		if ( isset( $this->options['options_check']['time'] ) && date( 'Ymd' ) == date( 'Ymd', $this->options['options_check']['time'] ) )
			return false;
		$server_response = $this->make_api_request( 'request/get-form-data' );
		$response = json_decode($server_response['body'], true);
		if ($response['result'] != 'success') {
			$this->add_notification( 'warning', __( 'Please check your Experitus credentials. They seem to be wrong.' ) );
			return false;
		}
		$result = false;
		if ( $this->options['request_attributes'] != $response['attributes'] )
			$result = true;
		if ( $this->options['request_items'] != $response['items'] )
			$result = true;
		if ( $this->options['payments_data'] != $response['payments_data'] )
			$result = true;
		if ($this->options['block_dates'] != $response['block_dates'] )
			$result = true;
		update_option( 'experitus_options_check', ['outdated' => $result, 'time' => time()] );
		return $result;
	}
	
	/** 
	 * Action callback
	 */
	public function action_connect() {
		if ( !current_user_can( 'manage_options' ) )
			return false;
		
		//get options
		$this->populateOptions();
		
		//check if options outdated
		$outdated = false;
		if ( $this->if_options_outdated() ) {
			$this->add_notification( 'warning', __( 'Your booking form options seem to be outdated. If so your booking form will not work properly. Please reload it by clicking button "Reload form attributes".' ) );
			$outdated = true;
		}
		
		//define current tab
		if ( isset( $_GET[ 'tab' ] ) ) {
			$this->current_tab = $_GET[ 'tab' ];
		}
		elseif (!$this->options['connection_data']) {
			$this->current_tab = 'experitus_credentials';
		}
		else {
			$this->current_tab = 'form_settings';
		}
		
		//check ssl for paid
		if ( isset( $this->options['request_attributes']['paid'] ) && $this->options['request_attributes']['paid'] && !is_ssl()) {
			$this->add_notification( 'warning', __( 'Payments cannot be done in your booking form because of not secure connection. Please enable https protocol.' ) );
		}
		
		//reload attributes
		if ( strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' && isset( $_POST['reload_form_attributes'] ) && check_admin_referer( 'experitus_reload_attributes', 'experitus_admin_non_ce' ) ) {
			$this->reload_form_attributes();
		}
		
		//render html
		wp_enqueue_script( 'experitus-admin', plugins_url( 'web/experitus-admin.js', EXPERITUS_ROOT_FILE ), array('jquery'), '1.0.0', true );
		$this->render( 'admin' );
	}
	
}

?>