<?php

require_once('experitus-base-controller.php');

/**
 * Plugin frontend controller
 *
 * @package Experitus Orders Page
 * @since 0.1
 */

class experitusFormController extends experitusBaseController {
	
	/**
	 * Holds payment method. Null if payments disabled.
	 */
	public $payment_method = null;
	
	/** 
	 * Renders order form itself and hamdles with it's submissions
	 */
	public function handle_shortcode() {
		
		//get options
		$this->populateOptions();
		
		//add payments
		if ( is_ssl() && $this->options['payments_data'] && isset( $this->options['payments_data']['gateway'] ) ) {
			if ( $this->options['payments_data']['gateway'] == 'paypal' ) {
				$this->payment_method = 'paypal';
			}
			elseif ( $this->options['payments_data']['gateway'] == 'stripe' && isset( $this->options['payments_data']['stripe_public_key'] ) ) {
				$this->payment_method = 'stripe';
			}
		}
		
		//submitted form processing
		if ( isset( $_POST['Request'] ) && isset( $_POST['RequestItem'] ) ) {
			if ( !wp_verify_nonce( $_POST['experitus_non_ce'], 'experitus_order_request' ) ) {
				wp_nonce_ays ( 'experitus_order_request' );
			}
			else {
				$validation_result = $this->validate_form();
				if ( $validation_result === true ) {
					$request_data = $this->sanitize_form($_POST['Request']);
					$request_data['items'][0] = $this->sanitize_form($_POST['RequestItem'][0]);
					if ( $this->payment_method == 'stripe' && $_POST['stripe_token'] ) {
						$request_data['stripe_token'] = $_POST['stripe_token'];
					}
					elseif ( $this->payment_method == 'paypal' ) {
						$request_data['is_wordpress_plugin'] = 1;
						$request_data['referrer'] = urlencode( 'http'.(is_ssl() ? 's' : '').'://'.$_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI'] );
					}
					
					$server_response = $this->make_api_request('request/add', 'POST', null, $request_data);
					
					if ( is_wp_error( $server_response ) ) {
						$this->add_notification( 'error', $server_response->get_error_message() );
					}
					else {
						$response = json_decode($server_response['body'], true);
						if ( $this->payment_method == 'paypal' && $response['result'] == 'top_redirect' ) {
							echo '<script type="text/javascript">window.location.href="'.$response['redirect_url'].'"</script>';
							exit;
						}
						if ($response['result'] == 'success') {
							$this->add_notification( 'success', __( 'Your order was successfully submited! We will contact you as soon as possible.' ) );
						}
						elseif (isset( $response['errors'] )) {
							$this->add_notification( 'error', implode( ' ', $response['errors'] ) );
						}
						else {
							$this->add_notification( 'error', __( 'Something went wrong! Your order was not submitted.' ) ); 
						}
					}
				}
			}
		}
		
		// after paypal case
		if ( isset( $_GET['referrer_paypal'] ) && isset( $_GET['request_id'] ) ) {
			$this->add_notification( 'success', __( 'Your order was successfully submited! We will contact you as soon as possible.' ) );
			$server_response = $this->make_api_request( 'request/get', 'GET', null, ['id' => $_GET['request_id']] );
			$response = json_decode( $server_response['body'], true );
			if ( $response['result'] == 'success' ) {
				$_POST['Request'] = $response['request'];
				$_POST['RequestItem'][0] = $response['item'];
				$_POST['RequestItem'][0]['date'] = date('m/d/Y', $response['item']['time']);
				$_POST['RequestItem'][0]['time'] = date('H:i', $response['item']['time']);
			}
		}
		
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script('jquery-ui-selectmenu');
		wp_enqueue_script( 'jquery-ui-spinner' );
		wp_enqueue_script( 'experitus-form-scripts', plugins_url( 'web/experitus-form.js', EXPERITUS_ROOT_FILE ), array('jquery'), '1.0.0', true );
		wp_enqueue_script( 'jquery-timepicker', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.8.11/jquery.timepicker.min.js', array('jquery'), '1.0.0', true );
		wp_enqueue_style( 'jquery-timepicker-style', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.8.11/jquery.timepicker.min.css' );
		wp_enqueue_style( 'jqueryui-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css' );
		wp_enqueue_style( 'experitus-form-styles', plugins_url( 'web/experitus-form.css', EXPERITUS_ROOT_FILE ) );
		if ($this->payment_method == 'stripe')
			wp_enqueue_script('stripe-script', 'https://checkout.stripe.com/checkout.js');

		$this->render( 'form' );
	}
	
	/** 
	 * Validates orders form
	 */
	private function validate_form() {
		if ( $this->if_captcha_enabled() && !$this->validate_captcha() )
			return false;
		$request = $_POST['Request'];
		$item = $_POST['RequestItem'];
		$errors = array();
		foreach ( $this->get_required_attributes() as $required_attribute => $required_label ) {
			$hasRequired = true;
			if ( !isset( $request[$required_attribute] ) || !$request[$required_attribute] )
				$hasRequired = false;
			if ( !$hasRequired && isset( $item[$required_attribute] ) && $item[$required_attribute] )
				$hasRequired = true;
			if (!$hasRequired)
				$errors[] = __( $required_label . ' field cannot be empty.' );
		}
		$attributes = $this->get_all_attributes();
		if ( isset($request['email']) && $request['email'] && !is_email( $request['email'] ) )
			$errors[] = __( $attributes['email']['label'] . ' field needs a valid email address.' );
		if ( isset($request['stay_duration']) && $request['stay_duration'] && !is_numeric( $request['stay_duration'] ) )
			$errors[] = __( $attributes['stay_duration']['label'] . ' field needs a numeric value.' );
		if ( isset($request['arrival_date']) && $request['arrival_date'] && !preg_match( '/\d{2}\/\d{2}\/\d{4}/',$request['arrival_date'] ) )
			$errors[] = __( $attributes['arrival_date']['label'] . ' field needs a valid date in mm/dd/yyyy format.' );
		if ( isset($request['phone']) && $request['phone'] && !preg_match( '/([0-9\s()+-]*){5,20}/', $request['phone'] ) )
			$errors[] = __( $attributes['phone']['label'] . ' field needs a valid phone number' );
		
		if ( isset($item['inventory_id']) && $item['inventory_id'] && !is_numeric( $item['inventory_id'] ) )
			$errors[] = __( $attributes['inventory_id']['label'] . ' has to be chosen from a list.' );
		if ( isset($item['date']) && $item['date'] && !preg_match( '/\d{2}\/\d{2}\/\d{4}/',$item['date'] ) )
			$errors[] = __( $attributes['date']['label'] . ' field needs a valid date in mm/dd/yyyy format.' );
		if ( isset($item['time']) && $item['time'] && !preg_match( '/\d{2}:\d{2}/',$item['time'] ) )
			$errors[] = __( $attributes['time']['label'] . ' field needs a valid time in hh:mm format.' );
		if ( isset($item['adults']) && $item['adults'] && !is_numeric( $item['adults'] ) )
			$errors[] = __( $attributes['adults']['label'] . ' field needs a numeric value.' );
		if ( isset($item['children']) && $item['children'] && !is_numeric( $item['children'] ) )
			$errors[] = __( $attributes['children']['label'] . ' field needs a numeric value.' );
		
		if ( count($errors) > 0 ) {
			$this->add_notification( 'error', implode( ' ', $errors) );
			return false;
		}
		
		return true;
	}
	
	
	/** 
	 * Validates Google reCAPTCHA
	 */
	private function validate_captcha() {
		if ( !isset( $_POST['g-recaptcha-response'] ) || !$_POST['g-recaptcha-response']) {
			$this->add_notification( 'error', __('To submit form please confirm you are not a robot.') );
			return false;
		}
		$params = array(
			'secret' => $this->options['captcha_data']['secret_key'],
			'response' => $_POST['g-recaptcha-response']
		);
		$result_array = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array('body' => $params) );
		$result = json_decode( $result_array['body'] );
		if (!$result->success)
			$this->add_notification( 'error', __('You failed to pass Captcha test. This form is for humans only.') );
		return $result->success;
	}
	
	/** 
	 * Sanitizes orders form input
	 */
	private function sanitize_form($data) {
		$sanitized = array();
		foreach ( $data as $attribute => $value ) {
			if ( in_array( $attribute, array('email', 'inventory_id', 'adults', 'children', 'arrival_date', 'stay_duration', 'date', 'time', 'phone') ) )
				$sanitized[$attribute] = $value;
			else
				$sanitized[$attribute] = sanitize_text_field( $value );
		}
		return $sanitized;
	}
	
	
	/** 
	 * Returns array of all attributes
	 */
	private function get_all_attributes() {
		$attributes = [];
		foreach ($this->options['request_attributes']['request'] as $categoryAttributes) {
			$attributes = array_merge($attributes, $categoryAttributes);
		}
		array_merge($attributes, $this->options['request_attributes']['item']);
		return $attributes;
	}
	
	
	/** 
	 * Returns array of required request attributes
	 */
	private function get_required_attributes() {
		$attributes = $this->get_all_attributes();
		$required = array();
		foreach ($attributes as $attribute => $attribute_data) {
			if ($attribute_data['required'])
				$required[$attribute] = $attribute_data['label'];
		}
		return $required;
	}
	
}