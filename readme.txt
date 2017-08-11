=== CPT Contact Form ===
Contributors: geomagas
Tags: contact form, shortcode, custom post type
Requires at least: 3.0.1
Tested up to: 3.9.1
Stable tag: 0.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate Link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5SD6XG9JD5TS8

A contact form that utilizes a custom post type to host messages on-site instead of directly sending them by e-mail.

== Description ==

This plugin, like many others, provides a shortcode to add a simple contact form in any page of your site. The difference
is that messages are stored on-site, using a custom post type. Instead of sending the whole message by e-mail, just a 
notification is sent in configurable intervals.

The shortcode syntax is the following:

	[cpt-contact-form]

Please note that no default styling is provided, so if the form looks crappy, you'll have to find a way to provide the 
relevant css yourself, by adjusting your child theme's style.css or otherwise.

When a message is stored, shortcodes are stripped and html attributes are escaped. Although, I'm planning to add a configuration
option for that in the future.

The plugin is in (hopefully correct) English, but a Greek translation is provided as well.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the `cpt-contact-form` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Flush the reqrite rules by visiting the permalinks page in Settings
4. Place a `[cpt-contact-form]` shortcode on the page(s) you want the form to appear

== Screenshots ==

1. The plugin's Settings screen

== Changelog ==

= 0.1.0 =
* First release.

= 0.2.0 =
* Added message "read" status (read/unread).
* Added Settings admin screen with several options.
* Changed e-mail notifications to be sent in regular intervals

= 0.2.1 =
* Added missing files

= 0.2.2 =
* Fixed a minor bug
