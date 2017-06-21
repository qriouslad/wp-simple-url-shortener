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

/**
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


/**
 * Displaying a shortnened URL in a meta box
 *
 * @see https://developer.wordpress.org/plugins/metadata/custom-meta-boxes/
 * @see https://www.sitepoint.com/the-wordpress-http-api/
 * @return string $shortlink Short goo.gl URL
 */
function wpsus_meta_box_markup($object) {

	// Retrieve the long URL of the post using the get_permalink() function
	$key = get_permalink($object->ID);

	// Check if API Key has been added to the settings page / stored in options table
	if(get_option('wpsus-input-field', '') != '') {

		// Check if we already have a short URL of this long URL in the database as a WordPress option. 
		// option_name (key) is the long URL, option_value (value) is the short URL

		// If short URL exist,  we use that

		if(get_option($key, '') != '') {

			echo get_option($key, '');
			return;

		}

		// If not, then we create and retrieve Short URL using the HTTP API and store it as a WordPress option

		$url = 'https://www.googleapis.com/urlshortener/v1/url';

		$result = wp_remote_post(

			add_query_arg(
				'key',
				get_option('wpsus-input-field'),
				'https://www.googleapis.com/urlshortener/v1/url'
			),
			array(
				'body' => json_encode(array('longUrl' => esc_url_raw($key))),
				'headers' => array( 'Content-Type' => 'application/json')
			)

		);

		if(is_wp_error($result)) {
			echo "Error";
			return;
		}

		$result = json_decode($result['body']);
		$shortlink = $result->id;

		update_option($key, $shortlink);

		echo $shortlink;

	}

}

// Create a meta box using the add_meta_box function
function wpsus_meta_box() {

	add_meta_box('wpsus-meta-box', 'Short URL', 'wpsus_meta_box_markup', 'post', 'side', 'default', null);
}

add_action('add_meta_boxes', 'wpsus_meta_box');


/**
 * Display short URL in the front end
 *
 * @return string $content The post content appended with short URL section
 */
function wpsus_content_filter($content) {

	// Check if post type is 'post', if so, stop executing function
	if($GLOBALS['post']->post_type != 'post') {
		return;
	}

	// Retrieve the long URL of the post using the get_permalink() function
	$key = get_permalink($GLOBALS['post']->ID);

	// Check if API Key has been added to the settings page / stored in options table
	if(get_option('wpsus-input-field') != '') {

		// Check if we already have a short URL of this long URL in the database as a WordPress option. 
		// option_name (key) is the long URL, option_value (value) is the short URL

		// If short URL exist,  we append it to the end of the content

		if(get_option($key, '') != '') {

			// Append short URL after post content
			$content = $content . '<p><strong>Short URL for this post:</strong>  ' . get_option($key, '') . '</p>';
			return $content;
		}

		// If not, then we create and retrievie Short URL using the HTTP API and store it as a WordPress option

		$url = 'https://www.googleapis.com/urlshortener/v1/url';

		$result = wp_remote_post(

			add_query_arg(
				'key',
				get_option('wpsus-input-field'),
				'https://www.googleapis.com/urlshortener/v1/url'
			),
			array(
				'body' => json_encode(array('longUrl' => esc_url_raw($key))),
				'headers' => array( 'Content-Type' => 'application/json' )
			)

		);

		if(is_wp_error($result)) {
			echo 'Error';
			return;
		}

		$result = json_decode($result['body']);
		$shortlink = $result->id;

		update_option($key, $shortlink);

		// Append short URL after post content
		$content = $content . '<p><strong>Short URL for this post:</strong> ' . get_option($key, '') . '</p>';
		return $content;

	}

}

add_filter('the_content', 'wpsus_content_filter');