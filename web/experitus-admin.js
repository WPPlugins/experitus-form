jQuery(document).ready(function() {
	function ifExperitusCaptchaEnabled() {
		if (jQuery('#experitus_enable_captcha:checked').length > 0) {
			jQuery('.experitus_captcha_credentials').prop('disabled', false);
		}
		else {
			
			jQuery('.experitus_captcha_credentials').prop('disabled', true);
		}
	}
	if (jQuery('#experitus_enable_captcha').length > 0) {
		ifExperitusCaptchaEnabled();
		jQuery(document).on('change', '#experitus_enable_captcha', function() {
			ifExperitusCaptchaEnabled();
		});
	}
});