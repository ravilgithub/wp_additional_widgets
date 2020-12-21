<?php
/**
 * Plugin Name: BRI Adittional Widgets
 * Plugin URI:  http://www.tstudio.zzz.com.ua
 * Description: Adittional Widgets
 * Version:     1.0.0
 * Author:      Ravil
 * Author URI:  http://www.tstudio.zzz.com.ua
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'bri_adittional_widgets' ) ) {
	function bri_adittional_widgets() {
		require_once 'includes/bri-tabbed-widget.php';
		register_widget( 'BRI_Tabbed_Widget' );

		require_once 'includes/bri-posts-thumbnails-widget.php';
		register_widget( 'BRI_Posts_Thumbnails_Widget' );
	}
	add_action( 'widgets_init', 'bri_adittional_widgets' );
}
