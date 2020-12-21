<?php
/**
 * Widget Name: BRI Posts Thumbnail Widget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
 
class BRI_Posts_Thumbnails_Widget extends WP_Widget {
	
	/**
	 * Constructor
	 *
	 * @return void
	 **/
	public function __construct() {
		$widget_ops = array(
			'classname' => 'bri-posts-thumbnails-widget',
			'description' => __( 'Description Posts Thumbnails Widget.' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'bri-posts-thumbnails-widget', __( 'BRI Posts Thumbnails' ), $widget_ops );

		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
	}
	
	/**
	 * Scripts
	 */
	public function scripts() {
		wp_enqueue_style( 'bri-posts-thumbnails-widget-css', plugins_url( 'assets/css/bri-posts-thumbnails-widget.css', dirname( __FILE__ ), false, '1.0', 'all' ) );
		wp_enqueue_script( 'bri-posts-thumbnails-widget-js', plugins_url( 'assets/js/bri-posts-thumbnails-widget.js', dirname( __FILE__ ), array( 'jquery' ), '1.0', true ) );
	}

	/**
	 * Outputs the HTML for this widget.
	 *
	 * @param array  An array of standard parameters for widgets in this theme 
	 * @param array  An array of settings for this widget instance 
	 * @return void Echoes it's output
	 **/
	public function widget( $args, $instance ) {
		
		/**
	 * Filters the default gallery output.
	 *
	 * If the filtered output isn't empty, it will be used instead of generating
	 * the default gallery template.
	 *
	 * @param string $output   The gallery output. Default empty.
	 * @param array  $attr     Attributes of the gallery shortcode.
	 * @param int    $instance Unique numeric ID of this gallery shortcode instance.
	 */
		$output = apply_filters( 'bri_posts_thumbnails_widget', '', $args, $instance );
		if ( $output != '' ) {
			echo $output;
			return;
		}

		if ( ! isset( $args[ 'widget_id' ] ) )
			$args[ 'widget_id' ] = $this->id;

		$title = ( ! empty( $instance[ 'title' ] ) ) ? $instance[ 'title' ] : __( 'Posts Thumbnails' );

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$posttype = ( ! empty( $instance[ 'posttype' ] ) ) ? $instance[ 'posttype' ] : 'post';
		$orderby = ( ! empty( $instance[ 'orderby' ] ) ) ? $instance[ 'orderby' ] : 'ID';
		$order = ( ! empty( $instance[ 'order' ] ) ) ? $instance[ 'order' ] : 'DESC';

		//echo $orderby . ' | ' , $order;
		
		$number = ( ! empty( $instance[ 'number' ] ) ) ? absint( $instance[ 'number' ] ) : 9;
		if ( ! $number )
			$number = 9;

		$perrow = ( ! empty( $instance[ 'perrow' ] ) ) ? absint( $instance[ 'perrow' ] ) : 3;
		if ( ! $perrow )
			$perrow = 3;

		$size = ( ! empty( $instance[ 'size' ] ) ) ? absint( $instance[ 'size' ] ) : 50;
		if ( ! $size )
			$size = 50;
		// $size .= 'px';

		$ratio = ( ! empty( $instance[ 'ratio' ] ) ) ? '1' : '0';
		if ( $ratio ) {
			$img_size = array( $size, 0 );
		} else {
			$img_size = array( $size, $size );
		}

		$interval =  ( ! empty( $instance[ 'interval' ] ) ) ?  abs( floatval( $instance[ 'interval' ] ) ) : 0;

		// 100 - ширина родительского блока
		// 1 - ширина миниатюр должна выщитываться без учёта "margin-right" последней миниатюры в строке.
		$thumbs_width = ( ( 100 - $interval * ( $perrow - 1 ) ) / $perrow );

		$query_args = array( 
			'post_type' => $posttype,
			'post_status' => 'publish',
			'posts_per_page' => $number, 
			//'posts_per_page' => -1, 
			'orderby'=> $orderby, 
			'order'=> $order,

			// 'meta_query' взято от сюда - https://wp-kama.ru/question/wp_query-vyiborka-zapisey-s-miniatyurami
			'meta_query' => array( array( 'key' => '_thumbnail_id' ) )

			//'cache_results' => false
		);
		$query = new WP_Query( $query_args );


		// echo '<pre>';
		// print_r( $query );
		// echo '</pre>';


		$output = $args[ 'before_widget' ];
		if ( $title ) {
			$output .= $args[ 'before_title' ] . $title . $args[ 'after_title' ];
		}

		$output .= '<ul>';


		if ( $query->have_posts() ) :
			$n = 1;
			
			while ( $query->have_posts() ) : $query->the_post();
				if ( has_post_thumbnail() ) {
					$class = '';
					$tooltip_html = '';
					$tooltip_title = '';
					$margin_right = $interval;

					if ( ( 1 === $n ) || ( 0 === ( $n - 1 ) % $perrow ) ) {
						$class = 'first';
					}

					if ( 0 === $n % $perrow ) {
						$class = 'last';
						$margin_right = 0;
					}

					if ( ! empty( $instance[ 'show_tooltip' ] ) ) {
						if ( ! empty( $instance[ 'tooltip_type' ] ) && ( 'tooltip_type_html' === $instance[ 'tooltip_type' ] ) ) {
							$tooltip_html = '<span class="bri-post-thumbnail-caption">' . get_the_title() . '</span>';
						} else {
							$tooltip_title = ' title="' . get_the_title() . '" ';
						}
					}

					// $post - глобальная ( в цикле )
					$output .= '<li class="' . $class . '" style="width:' . $thumbs_width . '%;margin-right:' . $margin_right . '%;margin-bottom:' . $interval . '%;"><a href="' . get_the_permalink() . '"' . $tooltip_title . '>' . get_the_post_thumbnail( $query->ID, $img_size, 'class=bri-post-thumbnail' ) . '</a>' . $tooltip_html . '</li>';
					
					$n++;
				}
			endwhile;
		endif;
		
		wp_reset_postdata();

		$output .= '</ul>';
		$output .= $args[ 'after_widget' ];
		echo $output;
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
		$instance[ 'title' ] = sanitize_text_field( $new_instance[ 'title' ] );
		$instance[ 'posttype' ] = sanitize_text_field( $new_instance[ 'posttype' ] );
		$instance[ 'orderby' ] = sanitize_text_field( $new_instance[ 'orderby' ] );
		$instance[ 'order' ] = sanitize_text_field( $new_instance[ 'order' ] );
		$instance[ 'number' ] = absint( $new_instance[ 'number' ] );
		$instance[ 'perrow' ] = absint( $new_instance[ 'perrow' ] );
		$instance[ 'size' ] = absint( $new_instance[ 'size' ] );
		$instance['ratio'] = !empty($new_instance['ratio']) ? 1 : 0;
		$instance[ 'interval' ] = abs( floatval( $new_instance[ 'interval' ] ) );
		$instance['show_tooltip'] = !empty($new_instance['show_tooltip']) ? 1 : 0;
		$instance[ 'tooltip_type' ] = sanitize_text_field( $new_instance[ 'tooltip_type' ] );
		return $instance;
	}
	
	/**
	 * Displays the form for this widget on the Widgets page of the WP Admin area.
	 *
	 * @param array  An array of the current settings for this widget
	 * @return void Echoes it's output
	 **/
	public function form( $instance ) {
		$title = isset( $instance[ 'title' ] ) ? esc_attr( $instance[ 'title' ] ) : '';
		$posttype = isset( $instance[ 'posttype' ] ) ? esc_attr( $instance[ 'posttype' ] ) : 'post';
		$orderby = isset( $instance[ 'orderby' ] ) ? esc_attr( $instance[ 'orderby' ] ) : 'ID';
		$order = isset( $instance[ 'order' ] ) ? esc_attr( $instance[ 'order' ] ) : 'DESC';
		$number = isset( $instance[ 'number' ] ) ? absint( $instance[ 'number' ] ) : 9;
		$perrow = isset( $instance[ 'perrow' ] ) ? absint( $instance[ 'perrow' ] ) : 3;
		$size = isset( $instance[ 'size' ] ) ? absint( $instance[ 'size' ] ) : 50;
		$ratio = isset( $instance['ratio'] ) ? (bool) $instance['ratio'] : false;
		$interval = isset( $instance[ 'interval' ] ) ? abs( floatval( $instance[ 'interval' ] ) ) : 0;
		$show_tooltip = isset( $instance['show_tooltip'] ) ? (bool) $instance['show_tooltip'] : false;
		$tooltip_type = isset( $instance[ 'tooltip_type' ] ) ? esc_attr( $instance[ 'tooltip_type' ] ) : 'tooltip_type_title';

		$args = array (
			'public' => true,
		);
		$post_types = get_post_types( $args, 'object' );

		$orderby_arr = array( 'ID' => 'ID', 'author' => 'Author', 'title' => 'Title', 'name' => 'Slug', 'date' => 'Create date', 'modified' => 'Modified date', 'rand' => 'Random', 'comment_count' => 'Popularity', 'menu_order' => 'Menu order' );

		$order_arr = array( 'DESC' => 'Descending', 'ASC' => 'Ascending' );
?>
		<!-- Title -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">
				<?php _e( 'Title:' ); ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>


		<!-- Select Post Types -->
		<p>
			<label for="<?php echo $this->get_field_id( 'posttype' ); ?>">
				<?php _e( 'Post Type:' ); ?>
			</label>
			<select name="<?php echo esc_attr( $this->get_field_name( 'posttype' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'posttype' ) ); ?>" class="widefat">
<?php foreach ( $post_types as $obj ) : ?>
				<?php if ( 'attachment' === $obj->name ) continue; ?>
				<option value="<?php echo $obj->name; ?>"<?php selected( $posttype, $obj->name ); ?>>
					<?php echo $obj->name; ?>
				</option>
<?php endforeach; ?>
			</select>
		</p>


		<!-- Select Orderby -->
		<p>
			<label for="<?php echo $this->get_field_id( 'orderby' ); ?>">
				<?php _e( 'Orderby:' ); ?>
			</label>
			<select name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'orderby' ) ); ?>" class="widefat">
<?php foreach ( $orderby_arr as $key => $value) : ?>
				<option value="<?php echo $key; ?>"<?php selected( $orderby, $key ); ?>>
					<?php _e( $value ); ?>
				</option>
<?php endforeach; ?>
			</select>
		</p>


		<!-- Select Order -->
		<p>
			<label for="<?php echo $this->get_field_id( 'order' ); ?>">
				<?php _e( 'Order:' ); ?>
			</label>
			<select name="<?php echo esc_attr( $this->get_field_name( 'order' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'order' ) ); ?>" class="widefat">
<?php foreach ( $order_arr as $key => $value ) : ?>
				<option value="<?php echo $key; ?>"<?php selected( $order, $key ); ?>>
					<?php _e( $value ); ?>
				</option>
<?php endforeach; ?>
			</select>
		</p>

		<!-- Number of post thumbnails -->
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>">
				<?php _e( 'Number of thumbnails to show:' ); ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1" value="<?php echo $number; ?>" size="3" />
		</p>


		<!-- Number of post thumbnails on ROW -->
		<p>
			<label for="<?php echo $this->get_field_id( 'perrow' ); ?>">
				<?php _e( 'Thumbnails per row:' ); ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'perrow' ); ?>" name="<?php echo $this->get_field_name( 'perrow' ); ?>" type="number" step="1" min="1" value="<?php echo $perrow; ?>" size="3" />
		</p>

		<!-- Post thumbnails size -->
		<p>
			<label for="<?php echo $this->get_field_id( 'size' ); ?>">
				<?php _e( 'Post thumbnails size ( px ):' ); ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>" type="number" step="10" min="50" value="<?php echo $size; ?>" size="3" />
		</p>

		<!-- Save images ratio -->
		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('ratio'); ?>" name="<?php echo $this->get_field_name('ratio'); ?>"<?php checked( $ratio ); ?> />
			<label for="<?php echo $this->get_field_id( 'ratio' ); ?>">
				<?php _e( 'Save images ratio?' ); ?>
			</label>
		</p>

		<!-- Post thumbnails Interval -->
		<p>
			<label for="<?php echo $this->get_field_id( 'interval' ); ?>">
				<?php _e( 'Post thumbnails interval ( % ):' ); ?>
			</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'interval' ); ?>" name="<?php echo $this->get_field_name( 'interval' ); ?>" type="number" step="0.01" min="0" value="<?php echo $interval; ?>" size="3" />
		</p>

		<hr />

		<!-- Show tooltip -->
		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'show_tooltip' ); ?>" name="<?php echo $this->get_field_name( 'show_tooltip' ); ?>"<?php checked( $show_tooltip ); ?> />
			<label for="<?php echo $this->get_field_id( 'show_tooltip' ); ?>">
				<?php _e( 'Show tooltip?' ); ?>
			</label>
		</p>

		<!-- Tooltip type -->
		<p>
			<input type="radio" class="widefat radio" id="<?php echo $this->get_field_id( 'tooltip_type_title' ); ?>" name="<?php echo $this->get_field_name( 'tooltip_type' ); ?>" value="tooltip_type_title" <?php echo ( $tooltip_type === 'tooltip_type_title' ) ? 'checked' : ''; ?> />
			<label for="<?php echo $this->get_field_id( 'tooltip_type_title' ); ?>">
				<?php _e( 'Attribute title' ); ?>
			</label><br />

			<input type="radio" class="widefat radio" id="<?php echo $this->get_field_id( 'tooltip_type_html' ); ?>" name="<?php echo $this->get_field_name( 'tooltip_type' ); ?>" value="tooltip_type_html" <?php echo ( $tooltip_type === 'tooltip_type_html' ) ? 'checked="checked"' : ''; ?> />
			<label for="<?php echo $this->get_field_id( 'tooltip_type_html' ); ?>">
				<?php _e( 'HTML' ); ?>
			</label>
		</p>
<?php	
	}
}
