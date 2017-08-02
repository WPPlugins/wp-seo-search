<?php
/*
* Plugin Name: WP SEO Search
* Description: Get a better permalink for your search results page.
* Version: 0.2.1
* Author: Angelica Costa
* Author URI: http://webangie.com/
* Text Domain: wpseosearch
* Domain Path: /languages/
*/

load_plugin_textdomain( 'wpseosearch', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

add_action( 'load-options-permalink.php', 'wpseosearch_settings' );
function wpseosearch_settings(){
	if( isset( $_POST['wpseosearch_base'] ) ){
		update_option( 'wpseosearch_base', sanitize_title_with_dashes( $_POST['wpseosearch_base'] ) );
	}
	add_settings_field( 'wpseosearch_base', __( 'Search Base', 'wpseosearch' ), 'wpseosearch_input', 'permalink', 'optional' );
}
function wpseosearch_input(){
	$value = get_option( 'wpseosearch_base', 'search');	
	echo '<input type="text" value="' . esc_attr( $value ) . '" name="wpseosearch_base" id="wpseosearch_base" class="regular-text" />';
}

function wpseosearch_base() {
	global $wp_rewrite;
	$wp_rewrite->search_base = get_option( 'wpseosearch_base', 'search' );
	$wp_rewrite->flush_rules();
}

add_action('init', 'wpseosearch_base');

function wpseosearch_rewrite() {
	global $wp_rewrite;
	if ( !isset( $wp_rewrite ) || !is_object( $wp_rewrite ) || !$wp_rewrite->using_permalinks() )
		return;
	$search_base = $wp_rewrite->search_base;
	if ( is_search() && !is_admin() && strpos( $_SERVER['REQUEST_URI'], "/{$search_base}/" ) === false ) {
		wp_redirect( home_url( "/{$search_base}/" . urlencode( get_query_var( 's' ) ) ) );
		exit();
	}
}
add_action( 'template_redirect', 'wpseosearch_rewrite' );

if ( version_compare( $wp_version, '3.5', '<=' ) ) {
	function rewrite_seo_bug( $q ) {
		if ( $q->get( 's' ) && empty( $_GET['s'] ) && is_main_query() )
			$q->set( 's', urldecode( $q->get( 's' ) ) );
	}
	add_action( 'pre_get_posts', 'rewrite_seo_bug' );
}
?>
