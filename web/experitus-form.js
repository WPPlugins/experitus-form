jQuery(document).ready(function() {
	var experitusForm = jQuery("#experitus_request_form");
	var experitusFormFields = jQuery('#experitus_request_form input[name^="Request"], #experitus_request_form select[name^="Request"], #experitus_request_form textarea[name^="Request"]');
	var experitusFormData = jQuery('#expertus-form-data').data();
	var experitusSubmitButton = jQuery('#experitus_request_form button[type="submit"]');
	if (experitusFormData.pay && experitusFormData.stripeKey) {
		var stripeHandler = StripeCheckout.configure({
			key: experitusFormData.stripeKey,
			image: 'https://app.experitus.io/images/billing_logo.png',
			locale: 'auto',
			token: function(token) {
				jQuery('.stripe_token').val(token.id);
				experitusForm.unbind('submit').submit();
			}
		});
	}
	var experitusFormFns = {
		checkAvailability: function() {
			var inventoryId = jQuery('.request_item_inventory_id').val();
			var date = jQuery('.request_item_date').val();
			var time = jQuery('.request_item_time').val();
			var hint = jQuery('#experitus_request_item_0_field_inventory_id div.hint-block');
			if (hint.length == 0) {
				hint = jQuery('<div/>', { class: 'hint-block' });
				jQuery('#experitus_request_item_0_field_inventory_id').append(hint);
			}
			if (!inventoryId) {
				hint.empty();
				return false;
			}
			data = { inventory_id: inventoryId };
			if (date && time) {
				data.date = date;
				data.time = time;
			}
			jQuery.ajax({
				url: 'https://app.guidista.dev/en/'+experitusFormData.alias+'/check-availability/',
				type: 'GET',
				data: data,
				success: function(response) {
					if (response.result) hint.text(response.result);
					else hint.empty();
				},
				error: function(xhr, status, errorThrown) {
					console.log(status);
				}
			});
		},
		countPrice: function() {
			var item = jQuery('.request_item_inventory_id').val();
			if (!item) {
				return 0;
			}
			var price = experitusFormData.prices[item];
			if (price) {
				if (experitusFormData.priceTypes[item] == 'per_person') {
					price = price * experitusFormFns.countCustomers();
				}
				if (experitusFormData.paymentType == 'deposit' && !isNaN(experitusFormData.deposits[item])) {
					price = price * experitusFormData.deposits[item] / 100;
				}
				return price.toFixed(2)
			} else {
				return 0;
			}
		},
		showPrice: function() {
			var price = experitusFormFns.countPrice();
			if (price) {
				experitusSubmitButton.html('Pay ' + experitusFormData.currency + ' ' + price);
			}
			else {
				experitusSubmitButton.html('Submit');
			}
		},
		countCustomers: function() {
			var children = parseInt(jQuery('.request_item_children').val());
			children = isNaN(children) ? 0 : children;
			var adults = parseInt(jQuery('.request_item_adults').val());
			adults = isNaN(adults) ? 0 : adults;
			var customers = children + adults;
			return customers ? customers : 1;
		},
		validateForm: function() {
			var result = true;
			jQuery.each(experitusFormFields, function(key, field) {
				if (!experitusFormFns.validateField(jQuery(field))) {
					result = false;
				}
			});
			return result;
		},
		validateField: function(field) {
			fieldContainer = field.closest('.experitus_request_field');
			//validate required
			if (fieldContainer.hasClass('is-required') && !field.val()) {
				fieldContainer.addClass('has-error');
				return false;
			}
			//validate email
			if (field.is('.request_email')) {
				var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
				if (!regex.test(field.val())) {
					fieldContainer.addClass('has-error');
					return false;
				}
			}
			//valiate phone
			if (field.is('.request_phone')) {
				var regex = /^([0-9\s()+-]*){5,20}$/;
				if (!regex.test(field.val())) {
					fieldContainer.addClass('has-error');
					return false;
				}
			}
			//validate numbers
			if (field.is('.request_item_adults, .request_item_children, .request_stay_duration')) {
				var regex = /^[0-9]*$/;
				if (field.val()) {
					if (!regex.test(field.val())) {
						fieldContainer.addClass('has-error');
						return false;
					}
					if (field.is('.request_item_adults, .request_stay_duration') && field.val() < 1) {
						fieldContainer.addClass('has-error');
						return false;
					}
				}
			}
			//validate date
			if (field.is('.request_item_date, .request_arrival_date')) {
				if (field.val()) {
					var regex = /^[0-9]{2}[\/][0-9]{2}[\/][0-9]{4}$/;
					if (!regex.test(field.val())) {
						fieldContainer.addClass('has-error');
						return false;
					}
				}
			}
			//validate time
			if (field.is('.request_item_time')) {
				var regex = /^[0-9]{2}[\:][0-9]{2}$/;
				if (field.val() && !regex.test(field.val())) {
					fieldContainer.addClass('has-error');
					return false;
				}
			}
			fieldContainer.removeClass('has-error');
			return true;
		},
		pay2Stripe: function() {
			stripeHandler.open({
				name: 'Experitus',
				email: jQuery('.request_email').val(),
				description: '',
				currency: experitusFormData.currency,
				panelLabel: 'Pay',
				amount: experitusFormFns.countPrice() * 100,
				allowRememberMe: !1
			});
		},
		initDatePicker: function(field) {
			var blockDates = field.attr('data-block-dates');
			data = {
				dateFormat: 'mm/dd/yy',
				minDate: 0
			};
			if (blockDates) {
				arr = blockDates.split(',');
				data.beforeShowDay = function(date) {
					var string = jQuery.datepicker.formatDate('mm/dd/yy', date);
					return [ arr.indexOf(string) == -1 ]
				};
			}
			field.datepicker(data);
		}
	}
	
	
	if (jQuery('.request_item_date').length > 0) {
		experitusFormFns.initDatePicker(jQuery('.request_item_date'));
	}
	if (jQuery('.request_arrival_date').length > 0) {
		jQuery('.request_arrival_date').datepicker({
			dateFormat: 'mm/dd/yy',
			minDate: 0
		});
	}
	if (jQuery('.request_item_time').length > 0) {
		jQuery('.request_item_time').timepicker({
			timeFormat: 'H:i'
		});
	}
	if (jQuery('.request_item_inventory_id').length > 0) {
		jQuery('.request_item_inventory_id').selectmenu({
			change: function() {
				if (experitusFormFns.validateField(jQuery(this))) {
					experitusFormFns.checkAvailability();
					if (experitusFormData.pay) {
						experitusFormFns.showPrice();
					}
				}
			}
		});
		jQuery(document).on('change', '.request_item_date, .request_item_time', function() {
			if (experitusFormFns.validateField(jQuery(this))) {
				experitusFormFns.checkAvailability();
			}
		});
	}
	if (jQuery('.request_country').length > 0) {
		jQuery('.request_country').selectmenu();
	}
	if (jQuery('.request_item_adults').length > 0) {
		jQuery('.request_item_adults').spinner({
			min: 1,
			numberFormat: "n",
			stop: function() {
				if (experitusFormFns.validateField(jQuery(this))) {
					experitusFormFns.showPrice();
				}
			}
		});
	}
	if (jQuery('.request_item_children').length > 0) {
		jQuery('.request_item_children').spinner({
			min: 0,
			numberFormat: "n",
			stop: function() {
				if (experitusFormFns.validateField(jQuery(this))) {
					experitusFormFns.showPrice();
				}
			}
		});
	}
	if (jQuery('.request_stay_duration').length > 0) {
		jQuery('.request_stay_duration').spinner({
			min: 1,
			numberFormat: "n"
		});
	}
	
	experitusFormFields.on('change blur', function() {
		if (!experitusFormFns.validateField(jQuery(this))) {
			return false;
		}
		
		if (jQuery(this).is('.request_item_inventory_id')) {
			experitusFormFns.showPrice();
		}
	});
	
	experitusForm.on('submit', function(e) {
		e.preventDefault();
		if (!experitusFormFns.validateForm()) {
			return false;
		}
		if (experitusFormData.pay && experitusFormData.stripeKey && !jQuery('.stripe_token').val()) {
			if (experitusFormData.prices[jQuery('.request_item_inventory_id').val()]) {
				experitusFormFns.pay2Stripe();
				return false;
			}
		}
		
		jQuery(this).unbind('submit').submit()
	});
});