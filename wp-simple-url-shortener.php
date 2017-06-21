<?php
/*
Plugin Name: WP Simple URL Shortener
Plugin URI:  https://github.com/qriouslad/wp-simple-url-shortener
Description: Simple URL shortener using Google’s URL Shortener API. Automaticall add short url for each post.
Version:     1.0
Author:      Bowo
Author URI:  https://bowo.io
Text Domain: wpsus
Domain Path: /languages
License:     GPL2
 
WP Simple URL Shortener is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
WP Simple URL Shortener is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with {Plugin Name}. If not, see {License URI}.
*/

/* Google Short URL API key: AIzaSyAdlHJA0F86Xq8EkkdmTN5BZlRpIcpVCMw 
 *
 * API explorer: https://developers.google.com/apis-explorer/?hl=en_US#p/urlshortener/v1/
 */

/*
 * Functions to create settings page to enter and save API key
 * 
 * @see https://developer.wordpress.org/plugins/settings/settings-api/
 */

function wpsus_settings_page() {
	add_settings_section('section', "Enter Key Details", null, 'wpsus');
	add_settings_field('wpsus-input-field', 'API Key', 'wpsus_input_field_display', 'wpsus', 'section');
	register_setting('section', 'wpsus-input-field');
}

function wpsus_input_field_display() {
 ?>
 	<input type="text" name="wpsus-input-field" value="<?php echo get_option('wpsus-input-field'); ?>" style="width:50%;" />
 <?php
}

add_action('admin_init', 'wpsus_settings_page');

function wpsus_page() {
	?>
		<div class="wrap">
			<h1>URL Shortener Settings</h1>
			<form method="post" action="options.php">
				<?php
					settings_fields('section');
					do_settings_sections('wpsus');
					submit_button();
				?>
			</form>
		</div>
	<?php
}

function wpsus_menu_item() {
	add_submenu_page('options-general.php', 'URL Shortener', 'URL Shortener', "manage_options", 'url-shortener', 'wpsus_page');
}

add_action('admin_menu', 'wpsus_menu_item');

