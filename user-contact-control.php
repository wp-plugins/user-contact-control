<?php
/*
Plugin Name: User Contact Control
Plugin URI: http://stephanieleary.com/
Description: Take control of the user profile's contact fields.
Author: Stephanie Leary
Version: 1.1
Author URI: http://stephanieleary.com/
Text Domain: user_contact_control
*/

function ucc_filter_contactmethod( $contactmethods ) {
	$options = get_option('user_contact_control');
  	return $options;
}
add_filter( 'user_contactmethods', 'ucc_filter_contactmethod', 10, 1 );

function ucc_validate_options( $input ) {
	$fields = explode( "\n", $input );
	$contacts = array();
	
	foreach ( $fields as $field ) {
		if ( !empty( $field ) ) {
			$key = sanitize_key( trim( $field ) );
			// back compat for original keys
			if ( $key == 'jabbergoogletalk' )
				$key = 'jabber';
			if ( $key == 'yahooim' )
				$key = 'yim';
			$label = esc_html( trim( $field ) );
			$contacts[$key] = $label;
		}
	}
	
	return $contacts;
}

function ucc_options_page() { ?>
	<div class="wrap">
		<h2><?php _e( 'User Contact Control', 'user_contact_control'); ?></h2>
		<form method="post" id="user_contact_control" action="options.php">
			<?php 
			settings_fields( 'user_contact_control' );
			$options = get_option( 'user_contact_control' );
			$defaults = _wp_get_user_contactmethods();
			
			$user_contactmethods = array_merge( $defaults, $options ) ;
			$labels = $output = '';
			if ( $user_contactmethods ) {
				foreach ( $user_contactmethods as $key => $name ) { 
					$labels .= $name . "\n";
					$output .= sprintf('<p><strong>%s:</strong> <code>%s</code></p>', $name, $key);
				}
			}
			?>
			<p><?php _e('Enter one contact field label per line.', 'user_contact_control'); ?></p>
			<p><textarea name="user_contact_control" rows="10"><?php echo esc_html( $labels ); ?></textarea></p>
			<p><?php 
			printf(__('Use <code><a href="%s">get_user_meta</a></code> or <code><a href="%s">get_the_author_meta</a></code> to use these fields in your theme or plugin. ', 
				'user_contact_control'), 'http://codex.wordpress.org/Function_Reference/get_user_meta', 'http://codex.wordpress.org/Function_Reference/get_the_author_meta' ); 
			_e('Your user meta keys are:', 'user_contact_control');
			?>
			</p>	
			<p><?php echo $output; ?></p>
			<?php submit_button(); ?>
		</form>
	</div> <!-- .wrap -->
	<?php 
}

// when uninstalled, remove option
function ucc_remove_options() {
	delete_option('user_contact_control');
}
register_uninstall_hook( __FILE__, 'ucc_remove_options' );
// for testing only
// register_deactivation_hook( __FILE__, 'ucc_remove_options' );

function ucc_initialize_options() {
	if ( false == get_option( 'user_contact_control' ) )
		add_option( 'user_contact_control' );
} 
add_action( 'admin_init', 'ucc_initialize_options' );

function ucc_add_pages() {
	// Add option page to admin menu
	$pg = add_options_page(__('Contact Control'), __('Contact Control', 'user_contact_control'), 'manage_options', basename(__FILE__), 'ucc_options_page');
	// register setting
	register_setting( 'user_contact_control', 'user_contact_control', 'ucc_validate_options');
}
add_action('admin_menu', 'ucc_add_pages');

// Add link to options page from plugin list
add_action('plugin_action_links_' . plugin_basename(__FILE__), 'ucc_plugin_actions');
function ucc_plugin_actions($links) {
	$new_links = array();
	$new_links[] = sprintf( '<a href="%s">%s</a>', 'options-general.php?page='.basename(__FILE__).'.php', __('Settings', 'user_contact_control') );
	return array_merge($new_links, $links);
}

// i18n
load_plugin_textdomain( 'user_contact_control', '', plugin_dir_path(__FILE__) . '/languages' );