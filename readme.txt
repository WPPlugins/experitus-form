=== Experitus Booking Form ===
Contributors: burlakko, alwex
Tags: Experitus, Booking, Booking System, Reservation, Reservation System, Online Booking, Booking Engine, Tours, Tour Operator, Booking Plugin, Reservation Plugin, Booking Software, Reservation Payment System, Activity Booking, Tour Booking, Availability, Payments, Bookings, Orders, Form, Embedded Form
Requires at least: 4.4.2
Tested up to: 4.4.2
Stable tag: 0.4
License: GPLv2 or later

The WordPress Plugin For Embedding Experitus Booking Forms On Your Website.

== Description ==

[Experitus](http://experitus.io/) is the all in one solution for tour guides. Boost & track online reviews from one platform, fully synchronized with tour guide scheduler & calendar, email manager, invoicing & CRM all in one. This plugin helps you to easily install the booking form on your web site, so your visitors could send their orders directly to your Experitus account.
= Features include =
* Embedding the form to custom pages. 
* Seamlessly blends in with your existing Wordpress theme design.
* Responsive, mobile-friendly booking processing.
* Support for ReCaptcha verification in the form.
* Support for short codes, or custom theme pages in Wordpress.

Experitus integrates seamlessly into Wordpress and does not force customers off to an external website to process bookings.  Experitus keeps consumer data secure and separate from Wordpress.

== Installation ==

To install and configure the plugin follow these simple steps.

1. Upload the plugin folder to the `/wp-content/plugins/` directory of your web site, or install the plugin in WordPress admin area.
2. Activate Experitus Form plugin in admin area.
3. Go to Tools -> Experitus Form screen to configure the plugin.
4. Enter your Experitus credentials which can be found at https://app.experitus.io/en/c/settings/api-key/
5. Create a new page (or use existing one) with a shortcode `[experitus_orders_form]` in it. Your orders form will be rendered on it automatically.
6. It is highly recommended to enable captcha validation to prevent automatical form submittings by hackers. You can do it in 'Google reCAPTCHA credentials' tab of plugin admin area.

== Configuration ==

1. Create your [Experitus account](http://experitus.io/request/ "Experitus Sign Up")
2. Create a new company and configure your account on Experitus.
3. Enable the Experitus requests and customize the form.
4. Enable the Experitus form widget in WordPress.
4. Create a Wordpress **Pages** and embed the Experitus booking form by using the shortcode: `[experitus_orders_form]` (see the plugin for more options to pass to the shortcode).

== Frequently Asked Questions ==

= Where can I find orders submitted with this form? =

You can find all orders form submissions in Requests section of your Experitus account.

= Does this plugin contains a payment form? =

Yes! But only with https protocol. 

= If I change form settings in my Experitus account will changes be displayed automatically on my Wordpress form? =

To reload your form on Wordpress site please push button 'Reload Form attributes' in 'Form settings' tab of plugin admin area.

= How to change the look and feel of booking form? =

Just edit your css and/or js. Check the smart way of editing Wordpress files [here](http://codex.wordpress.org/Editing_Files) and [here](http://codex.wordpress.org/Child_Themes)

== Screenshots ==
1. Experitus Dashboard
2. Experitus Request Customization
2. Booking Form integrated into Wordpress

== Changelog ==

= 0.4 Aug 02 2016 =
* Added new fields
* Added field categories
* Added deposit payments
* Added new types of additional fields

= 0.3 Jul 04 2016 =
* Added 'block dates' functionality

= 0.2 Jun 27 2016 =
* Added 'check availability' functionality
* Added 'hidden inputs' functionality
* Added payments

= 0.1 Apr 21 2016 =
* Initial public version