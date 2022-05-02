<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/Rafa410/
 * @since      1.0.0
 *
 * @package    Noticeboard
 * @subpackage Noticeboard/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Noticeboard
 * @subpackage Noticeboard/admin
 * @author     Rafa Soler <rafasoler10@gmail.com>
 */
class Noticeboard_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action( 'init', array( $this, 'register_custom_post_types' ));
		add_action( 'admin_menu', array( $this, 'add_plugin_to_admin_menu' ), 9 );

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Noticeboard_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Noticeboard_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/noticeboard-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Noticeboard_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Noticeboard_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/noticeboard-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register a custom post typed for the noticeboard
	 * 
	 * @since 1.0.0
	 */
	public function register_custom_post_types() {
		$labels = array(
			'name' 					=> __( 'Announcements', 'noticeboard' ),
			'singular_name' 		=> __( 'Announcement', 'noticeboard' ),
			'add_new' 				=> __( 'Add Announcement', 'noticeboard' ),
			'add_new_item' 			=> __( 'Add New Announcement', 'noticeboard' ),
			'edit_item' 			=> __( 'Edit Announcement', 'noticeboard' ),
			'new_item' 				=> __( 'New Announcement', 'noticeboard' ),
			'view_item' 			=> __( 'View Announcement', 'noticeboard' ),
			'search_items' 			=> __( 'Search Announcement', 'noticeboard' ),
			'not_found' 			=> __( 'No Announcements found', 'noticeboard' ),
			'not_found_in_trash'	=> __( 'No Announcements found in trash', 'noticeboard' ),
		);
		$args = array(
			'labels' 		=> $labels,
			'public' 		=> true,
			'show_in_menu'	=> $this->plugin_name,
			'supports' 		=> array( 'title', 'editor', 'custom_fields'),
			'has_archive' 	=> false,
			'menu_icon' 	=> 'dashicons-megaphone',
			'rewrite'     	=> array( 'slug' => 'announcements' ),
		);
		
		register_post_type( 'nb_announcements', $args );
	}

	/**
	 * Add the admin menu for the plugin
	 * 
	 * @since 1.0.0
	 */
	 public function add_plugin_to_admin_menu() {
		add_menu_page( 
			'Noticeboard', 
			'Noticeboard',
			'administrator', 
			$this->plugin_name,
			array( $this, 'display_plugin_admin_dashboard' ),
			'dashicons-megaphone',
		);
	}

	/**
	 * Display the admin dashboard
	 * 
	 * @since 1.0.0
	 */
	public function display_plugin_admin_dashboard() {
    	require_once 'partials/noticeboard-admin-display.php';
	}

}
