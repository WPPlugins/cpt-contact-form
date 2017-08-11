<?php
/*
Plugin Name: CPT Contact Form
Author: geomagas
Version: 0.2.2
Description: A contact form that utilizes a custom post type to host messages on-site instead of directly sending them by e-mail. Only a notification is sent instead.
Text Domain: cpt-contact-form
*/

add_action(
	'plugins_loaded',
	function()
		{
		require_once(__DIR__."/cpt_contact_form.class.php");
		new cpt_contact_form(__FILE__);
		}
	);


