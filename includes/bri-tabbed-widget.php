<?php
/**
 * Widget Name: BRI Tabbed Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
 
class BRI_Tabbed_Widget extends WP_Widget {
	
	/**
	 * Constructor
	 *
	 * @return void
	 **/
	public function __construct() {
		$widget_ops = array(
			'classname' => 'bri-widget-tabbed',
			'description' => __( 'Description Tabbed Widget.' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'bri-tabbed-widget', __( 'BRI Tabbed Widget' ), $widget_ops );

		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
	}
	
	/**
	 * Scripts
	 */
	public function scripts() {
		wp_register_style( 'bri-tabbed-widget-css', plugins_url( 'assets/css/bri-tabbed-widget.css', dirname( __FILE__ ), false, '1.0', 'all' ) );
		wp_register_script( 'bri-tabbed-widget-js', plugins_url( 'assets/js/bri-tabbed-widget.js', dirname( __FILE__ ), array( 'jquery' ), '1.0', true ) );
	}

	/**
	 * Outputs the HTML for this widget.
	 *
	 * @param array  An array of standard parameters for widgets in this theme 
	 * @param array  An array of settings for this widget instance 
	 * @return void Echoes it's output
	 **/
	public function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		
		wp_enqueue_style( 'bri-tabbed-widget-css' );
		wp_enqueue_script( 'bri-tabbed-widget-js' );

		$tabs = ( ! empty( $instance['bri-tabs-num'] ) ) ? absint( $instance['bri-tabs-num'] ) : 2;
		if ( ! $tabs )
			$tabs = 2;

		$tabs_li_width = ( 100 / $tabs ) . '%';

		$html = $before_widget . '<div class="tabs-container"><ul class="tabs-list">';
		
		for ( $i = 0; $i < $tabs; $i++ ) {
			$active_class = ( 0 == $i ) ? ' active' : '';
			$title = ( ! empty( $instance[ 'title_' . $i ] ) ) ? $instance[ 'title_' . $i ] : __( 'Title' );
			$html .= '<li class="' . $active_class . '" style="width:' . $tabs_li_width . ';"><a href="#tabbed-widget-tab-' . $i . '">' . esc_attr( $title ) . '</a></li>';
		}
		
		$html .= '</ul><div class="tabs-content">';
		
		for ( $i = 0; $i < $tabs; $i++ ) {
			$active_class = ( 0 == $i ) ? ' active' : '';
			$contant = ( ! empty( $instance[ 'content_' . $i ] ) ) ? $instance[ 'content_' . $i ] : __( 'Content' );
			$html .= '<div class="tab-content-inner' . $active_class . '" id="tabbed-widget-tab-' . $i . '">' . apply_filters( 'bri_tab_content', $contant ) . '</div>';
		}
		$html .= '</div></div>';

		echo $html . $after_widget;
	}

	/**
	 * Deals with the settings when they are saved by the admin. Here is
	 * where any validation should be dealt with.
	 *
	 * @param array  An array of new settings as submitted by the admin
	 * @param array  An array of the previous settings 
	 * @return array The validated and (if necessary) amended settings
	 **/
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$tabs = $instance[ 'bri-tabs-num' ] = $new_instance[ 'bri-tabs-num' ];

		for ( $i = 0; $i < $tabs; $i++ ) {
			$title = esc_attr( $new_instance[ 'title_' . $i ] );
			if ( ! empty( $title ) ) {
				$instance[ 'title_' . $i ] = esc_attr( $new_instance[ 'title_' . $i ] );
			}
			$content = wp_kses_post( $new_instance[ 'content_' . $i ] );
			if ( ! empty( $content ) ) {
				$instance[ 'content_' . $i ] = wp_kses_post( $new_instance[ 'content_' . $i ] );
			}
		}
		return $instance;
	}
	
	/**
	 * Displays the form for this widget on the Widgets page of the WP Admin area.
	 *
	 * @param array  An array of the current settings for this widget
	 * @return void Echoes it's output
	 **/
	public function form( $instance ) {
		$defaults = array();
		$defaults[ 'bri-tabs-num' ] = 2;

		$tabs = ( ! empty( $instance[ 'bri-tabs-num' ] ) ) ? $instance[ 'bri-tabs-num' ] : $defaults[ 'bri-tabs-num' ];

		for ( $i = 0; $i < $tabs; $i++ ) {
			$defaults[ 'title_' . $i ] = '';
			$defaults[ 'content_' . $i ] = '';
		}
	
		$instance = wp_parse_args( ( array ) $instance, $defaults );
		
		for ( $i = 0; $i < $tabs; $i++ ) {
			echo '<p><label for="' . $this->get_field_id( 'title_' . $i ) . '">' . __( 'Title:' ) . '<input class="widefat" id="' . $this->get_field_id( 'title_' . $i ) .'" name="' . $this->get_field_name( 'title_' . $i ) . '" value="' . esc_attr( $instance[ 'title_' . $i ] ) . '" /></label></p>';
			echo '<p>' . __( 'Content:' ) . '<textarea style="width: 100%;" id="' . $this->get_field_id( 'content_' . $i ) . '" name="' . $this->get_field_name( 'content_' . $i ) . '">' . $instance[ 'content_' . $i ] . '</textarea></p>';
		}

		echo '<p><label for="tabs-num">' . __( 'Tabs number:' ) . '<input class="widefat" id="tabs-num" name="' . $this->get_field_name( 'bri-tabs-num' ) . '" type="number" step="1" value="' . $instance[ 'bri-tabs-num' ] . '" min="2" max="6" /></label></p>';
	}
}
