<?php function get_input_value($attribute) {
	if ( isset( $_POST['Request'][$attribute] ) )
		return $_POST['Request'][$attribute];
	if ( isset( $_POST['RequestItem'][0][$attribute] ) )
		return $_POST['RequestItem'][0][$attribute];
	return '';
} ?>

<div id="experitus_request_container">
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
	<?php $this->render('_notifications'); ?>
	
	<div style="display: none;" id="expertus-form-data"
		data-alias=<?= $this->options['connection_data']['company_alias']; ?>
		<?php if ( $this->payment_method ): ?>
			data-pay="1"
			<?= isset( $this->options['payments_data']['payment_type'] ) ? 'data-payment-type="'.$this->options['payments_data']['payment_type'].'"' : '' ?>
			<?= $this->payment_method == 'stripe' ? 'data-stripe-key="'.$this->options['payments_data']['stripe_public_key'].'"' : ''; ?>
			<?= isset( $this->options['payments_data']['prices'] ) ? 'data-prices="'.htmlspecialchars( json_encode( $this->options['payments_data']['prices'] ) ).'"' : '' ?>
			<?= isset( $this->options['payments_data']['price_types'] ) ? 'data-price-types="'.htmlentities( json_encode( $this->options['payments_data']['price_types'] ) ).'"' : '' ?>
			<?= isset( $this->options['payments_data']['deposits'] ) ? 'data-deposits="'.htmlentities( json_encode( $this->options['payments_data']['deposits'] ) ).'"' : '' ?>
			<?= isset( $this->options['payments_data']['currency'] ) ? 'data-currency="'.$this->options['payments_data']['currency'].'"' : '' ?>
		<?php else: ?>
			data-pay="0"
		<?php endif; ?>></div>
		
	
	<form id="experitus_request_form" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post">
		<?php wp_nonce_field( 'experitus_order_request', 'experitus_non_ce' ); ?>
		
		<?php foreach( $this->options['request_attributes']['request'] as $category => $categoryAttributes ): ?>
			<?php if (!$categoryAttributes) continue; ?>
			
			<div class="attributes_category" id="<?= $category ?>_category">
				<h3>
					<?php switch($category) {
						case 'customer':
							echo __( 'Customer' );
							break;
						case 'accommodation':
							echo __( 'Accommodation' );
							break;
					} ?>
				</h3>
				
				<?php foreach ($categoryAttributes as $attribute => $data): ?>
					
					<?php if ( isset( $data['type'] ) && $data['type'] == 'hidden_field' ): ?>
						<?php if ( isset( $_GET[$attribute] ) ): ?>
							<input class="request_<?php echo $attribute; ?>" value="<?php echo $_GET[$attribute]; ?>" type="hidden" id="request_<?php echo $attribute; ?>" name="Request[<?php echo $attribute; ?>]" />
						<?php endif; ?>
				
					<?php else: ?>
						<div class="experitus_request_field <?= $data['required'] ? 'is-required' : ''; ?>" id="experitus_request_field_<?php echo $attribute; ?>">
							<?php if ( !isset( $data['type'] ) || $data['type'] != 'checkbox' ): ?>
								<label for="request_<?php echo $attribute; ?>"><?php echo $data['label']; ?></label>
							<?php endif; ?>
							
							<?php if ( $attribute == 'country' ): ?>
								<select class="request_<?php echo $attribute; ?>" id="request_<?php echo $attribute; ?>" name="Request[<?php echo $attribute; ?>]">
									<option value=""></option>
									<?php foreach( $this->options['countries'] as $code => $country) { ?>
										<option value="<?php echo $code; ?>" <?php echo $code == get_input_value($attribute) ? 'selected="selected"' : ''; ?>><?php echo $country; ?></option>
									<?php } ?>
								</select>
							
							<?php elseif ( isset( $data['type'] ) && $data['type'] == 'text_area' ): ?>
								<textarea class="request_<?php echo $attribute; ?>" id="request_<?php echo $attribute; ?>" name="Request[<?php echo $attribute; ?>]"><?php echo get_input_value($attribute); ?></textarea>
							
							<?php elseif ( isset( $data['type'] ) && $data['type'] == 'checkbox' ): ?>
								<input class="request_<?php echo $attribute; ?>" type="checkbox" id="request_<?php echo $attribute; ?>" name="Request[<?php echo $attribute; ?>]" <?php echo get_input_value($attribute) ? 'checked="checked"' : ''; ?> />
								<label for="request_<?php echo $attribute; ?>"><?php echo $data['label']; ?></label>
							
							<?php else: ?>
								<input class="request_<?php echo $attribute; ?>" value="<?php echo get_input_value($attribute); ?>" type="text" id="request_<?php echo $attribute; ?>" name="Request[<?php echo $attribute; ?>]" />
							
							<?php endif; ?>
							
						</div>
					<?php endif; ?>
					
				<?php endforeach; ?>
				
			</div>
			
		<?php endforeach; ?>
		
		<div class="attributes_category" id="<?= $category ?>_category">
			<h3><?php echo __( 'Item' ); ?></h3>
			<?php foreach ( $this->options['request_attributes']['item'] as $attribute => $data ): ?>
				
				<?php if ( isset( $data['type'] ) && $data['type'] == 'hidden_field' ): ?>
					<?php if ( isset( $_GET[$attribute] ) ): ?>
						<input class="request_item_<?php echo $attribute; ?>" value="<?php echo $_GET[$attribute]; ?>" type="hidden" id="request_item_0_<?php echo $attribute; ?>" name="RequestItem[0][<?php echo $attribute; ?>]" />
					<?php endif; ?>
				
				<?php else: ?>
					<div class="experitus_request_field <?= $data['required'] ? 'is-required' : ''; ?>" id="experitus_request_item_0_field_<?php echo $attribute; ?>">
						<?php if ( !isset( $data['type'] ) || $data['type'] != 'checkbox' ): ?>
							<label for="request_item_0_<?php echo $attribute; ?>"><?php echo $data['label']; ?></label>
						<?php endif; ?>
					
						<?php if ( $attribute == 'comments' ): ?>
							<textarea class="request_item_<?php echo $attribute; ?>" id="request_item_0_<?php echo $attribute; ?>" name="RequestItem[0][<?php echo $attribute; ?>]"><?php echo get_input_value($attribute); ?></textarea>
						
						<?php elseif ( $attribute == 'inventory_id' ): ?>
							<select class="request_item_<?php echo $attribute; ?>" id="request_item_0_<?php echo $attribute; ?>" name="RequestItem[0][<?php echo $attribute; ?>]">
								<option value=""></option>
								<?php foreach( $this->options['request_items'] as $id => $item): ?>
									<option value="<?php echo $id; ?>" <?php echo $id == get_input_value($attribute) ? 'selected="selected"' : ''; ?>><?php echo $item; ?></option>
								<?php endforeach; ?>
							</select>
						
						<?php elseif ( $attribute == 'date' ): ?>
							<input class="request_item_<?php echo $attribute; ?>" value="<?php echo get_input_value($attribute); ?>" type="text" id="request_item_0_<?php echo $attribute; ?>" name="RequestItem[0][<?php echo $attribute; ?>]" data-block-dates="<?php echo $this->options['block_dates'] ? $this->options['block_dates'] : ''; ?>" />
						
						<?php elseif ( isset( $data['type'] ) && $data['type'] == 'text_area' ): ?>
							<textarea class="request_item_<?php echo $attribute; ?>" id="request_item_0_<?php echo $attribute; ?>" name="RequestItem[0][<?php echo $attribute; ?>]"><?php echo get_input_value($attribute); ?></textarea>
						
						<?php elseif ( isset( $data['type'] ) && $data['type'] == 'checkbox' ): ?>
							<input class="request_item_<?php echo $attribute; ?>" type="checkbox" id="request_item_0_<?php echo $attribute; ?>" name="RequestItem[0][<?php echo $attribute; ?>]" <?php echo get_input_value($attribute) ? 'checked="checked"' : ''; ?> />
							<label for="request_item_0_<?php echo $attribute; ?>"><?php echo $data['label']; ?></label>
						
						<?php else: ?>
							<input class="request_item_<?php echo $attribute; ?>" value="<?php echo get_input_value($attribute); ?>" type="text" id="request_item_0_<?php echo $attribute; ?>" name="RequestItem[0][<?php echo $attribute; ?>]" />
						
						<?php endif; ?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
		
		<?php if ( $this->if_captcha_enabled() ): ?>
			<div class="experitus_request_form_field" id="experitus_captcha_container">
				<div class="g-recaptcha" data-sitekey="<?php echo $this->options['captcha_data']['site_key']; ?>"></div>
			</div>
		<?php endif; ?>
		
		<?php if ($this->payment_method == 'stripe'): ?>
			<input value="" type="hidden" name="stripe_token" class="stripe_token" />
		<?php endif; ?>
		
		<div class="experitus_request_form_field" id="experitus_submit_button_container">
			<button type="submit" class="button button-primary button-large">Submit</button>
		</div>
	</form>
</div>