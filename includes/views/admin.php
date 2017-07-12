 <div class="wrap">
	<h1>Experitus Form</h1>
	<?php $this->render('_notifications'); ?>
	<h2 class="nav-tab-wrapper">
		<a href="?page=experitus-form&tab=form_settings" class="nav-tab <?php echo $this->current_tab == 'form_settings' ? 'nav-tab-active' : ''; ?>">Form settings</a>
		<a href="?page=experitus-form&tab=experitus_credentials" class="nav-tab <?php echo $this->current_tab == 'experitus_credentials' ? 'nav-tab-active' : ''; ?>">Experitus credentials</a>
		<a href="?page=experitus-form&tab=captcha_credentials" class="nav-tab <?php echo $this->current_tab == 'captcha_credentials' ? 'nav-tab-active' : ''; ?>">Google reCAPTCHA credentials</a>
	</h2>
	
	<?php if ( $this->current_tab == 'experitus_credentials' ): ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'experitus_connection_group' );
				do_settings_sections( 'experitus_connection_group' );
				submit_button(); ?>
		</form>
	<?php elseif ( $this->current_tab == 'captcha_credentials' ): ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'experitus_captcha_group' );
				do_settings_sections( 'experitus_captcha_group' );
				submit_button(); ?>
		</form>
	<?php else: ?>
		<p style="margin-top: 25px;">
			<strong><?php echo __( 'Important!' ); ?></strong>
			<?php echo __( 'To publish orders form you have to create a new page (or use an existing one) with a shortcode [experitus_orders_form] in it. Your form will be automatically rendered on this page.' ); ?>
		</p>
		<p>
			<?php echo __( 'If Request Form settings were changed on Experitus you can update them by clicking on a button below.' ); ?>
		</p>
		<form method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
			<?php wp_nonce_field( 'experitus_reload_attributes', 'experitus_admin_non_ce' ); ?>
			<?php submit_button( __( 'Reload form attributes' ), 'primary', 'reload_form_attributes'); ?>
		</form>
	<?php endif; ?>
	</form>
</div>