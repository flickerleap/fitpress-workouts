<?php
/**
 * @package FitPress
 */
/*
Plugin Name: FitPress Workouts
Plugin URI: http://fitpress.co.za
Description: FitPress Workouts is a add-on for FitPress that adds the ability to add workouts (WODs) and also show them on the booking platform
Version: 1.0
Author: Digital Leap
Author URI: http://digitalleap.co.za/wordpress/
License: GPLv2 or later
Text Domain: fitpress-invoices
*/

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;

function is_fitpress_active_for_workouts(){

	/**
	 * Check if WooCommerce is active, and if it isn't, disable Subscriptions.
	 *
	 * @since 1.0
	 */
	if ( !is_plugin_active( 'fitpress/fitpress.php' ) ) {
		add_action( 'admin_notices', 'FP_Workout::woocommerce_inactive_notice' );
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}

}

add_action( 'admin_init', 'is_fitpress_active_for_workouts' );

class FP_Workout {

	public function __construct(){

		add_action( 'init', array( $this, 'register_post_types' ), 5 );

		register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
		register_activation_hook( __FILE__, array( $this, 'flush_rewrite_rules' ) );

		add_filter( 'fitpress_before_day', array( $this, 'workout_link' ), 10, 2 );

		add_action( 'fitpress_before_account_sessions', array( $this, 'account_workout' ), 10 );

	}

	/**
	 * Register core post types.
	 */
	public static function register_post_types() {
		if ( post_type_exists('fp_workout') ) {
			return;
		}

		do_action( 'action_register_post_type' );

		register_post_type( 'fp_workout',
			array(
				'labels'             => array(
					'name'                  => __( 'Workouts', 'fitpress-workouts' ),
					'singular_name'         => __( 'Workout', 'fitpress-workouts' ),
					'menu_name'             => _x( 'Workouts', 'Admin menu name', 'fitpress-workouts' ),
					'add_new'               => __( 'Add Workout', 'fitpress-workouts' ),
					'add_new_item'          => __( 'Add New Workout', 'fitpress-workouts' ),
					'edit'                  => __( 'Edit', 'fitpress-workouts' ),
					'edit_item'             => __( 'Edit Workout', 'fitpress-workouts' ),
					'new_item'              => __( 'New Workout', 'fitpress-workouts' ),
					'view'                  => __( 'View Workout', 'f§itpress-workouts' ),
					'view_item'             => __( 'View Workout', 'fitpress-workouts' ),
					'search_items'          => __( 'Search Workouts', 'fitpress-workouts' ),
					'not_found'             => __( 'No Workouts found', 'fitpress-workouts' ),
					'not_found_in_trash'    => __( 'No Workouts found in trash', 'fitpress-workouts' ),
					'parent'                => __( 'Parent Workout', 'fitpress-workouts' ),
					'featured_image'        => __( 'Workout Image', 'fitpress-workouts' ),
					'set_featured_image'    => __( 'Set workout image', 'fitpress-workouts' ),
					'remove_featured_image' => __( 'Remove workout image', 'fitpress-workouts' ),
					'use_featured_image'    => __( 'Use as workout image', 'fitpress-workouts' ),
				),
				'description'         => __( 'This is where you can add new memberships to your website.', 'fitpress-workouts' ),
				'public'              => true,
				'show_ui'             => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => false,
				'hierarchical'        => false,
				'rewrite'             => array( 'slug' => 'workouts' ),
				'query_var'           => true,
				'has_archive'         => true,
				'show_in_nav_menus'   => true
			)
		);
	}

	public function flush_rewrite_rules( ){

		$this->register_post_types();
		flush_rewrite_rules();

	}

	public static function workout_link( $calendar = '', $running_day ){

		$args = array(
		'post_type' => 'fp_workout',
		'year'      => date( 'Y', $running_day ),
		'monthnum'  => date( 'm', $running_day ),
		'day'       => date( 'd', $running_day )
		);

		$workouts = new WP_Query( $args );

		if( $workouts->found_posts > 0 ):

			foreach( $workouts->posts as $workout ):

				$calendar .= '<p><a href="' . get_permalink( $workout->ID ) . '" class="button button-small">Workout of the Day</a></p>';

			endforeach;

		endif;

		return $calendar;

	}

	public static function account_workout( ){

		$args = array(
		'post_type' => 'fp_workout',
		'year'      => date( 'Y' ),
		'monthnum'  => date( 'm' ),
		'day'       => date( 'd' )
		);

		$workouts = new WP_Query( $args );

		if( $workouts->found_posts > 0 ):

			$todays_workout = '<h2>Today\'s Workout</h2>';

			foreach( $workouts->posts as $workout ):

				$todays_workout .= '<h3>'.$workout->post_title.'</h3>';
				$todays_workout .= apply_filters( 'the_content', $workout->post_content );

			endforeach;

			echo $todays_workout;

		endif;

	}

	public static function woocommerce_inactive_notice() {
		if ( current_user_can( 'activate_plugins' ) ) :?>
			<div id="message" class="error">
				<p><?php printf( __( '%sFitPress is inactive%s. The FitPress plugin must be active for FitPress Workouts to work. Please install & activate FitPress.', 'fitpress-invoices' ), '<strong>', '</strong>' ); ?></p>
			</div>
		<?php endif;
	}

}



/**
 * Extension main function
 */
function __fp_workouts_main() {
	new FP_Workout();
}

// Initialize plugin when plugins are loaded
add_action( 'plugins_loaded', '__fp_workouts_main' );
