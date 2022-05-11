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
		add_action( 'add_meta_boxes_nb_announcements', array( $this, 'setup_announcement_metaboxes' ));
		add_action( 'save_post_nb_announcements', array( $this, 'save_announcement_metabox_data') );

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
			'show_in_rest' 	=> true,
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

	public function setup_announcement_metaboxes() {
		add_meta_box(
			'ng_announcement_metaboxes', 
			'Camps personalitzats pels anuncis', 
			array($this,'ng_announcement_metaboxes'), 
			'nb_announcements',
			'normal',
			'high' 
		);
	}

	public function ng_announcement_metaboxes($post) {
		wp_nonce_field( 'nb_announcements_meta_box', 'nb_announcements_meta_box_nonce' );

		?>

		<div class="announcements_field_containers">
			<ul class="announcements_data_metabox">
				<li>
					<label for="announcement_summary"><?= __( 'Resum', 'noticeboard' ) ?></label>
					<textarea 
						name="announcement_summary" 
						id="announcement_summary"><?= get_post_meta( $post->ID, 'announcement_summary', true ); ?></textarea>
					<small><?= __( 'Si no s\'introdueix un resum, es generarà automàticament a partir del contingut.', 'noticeboard' ) ?></small>
				</li>
				<li>
					<label for="announcement_link"><?= __( 'Enllaç', 'noticeboard' ) ?></label>
					<input 
						type="url" 
						name="announcement_link" 
						id="announcement_link" 
						value="<?= get_post_meta( $post->ID, 'announcement_link', true ); ?>" 
						placeholder="https://www.example.com">
						<small><?= __( 'Per defecte és fa servir l\'enllaç de l\'anunci', 'noticeboard' ) ?></small>
				</li>
				<li>
					<label for="announcement_link_text"><?= __( 'Text de l\'enllaç', 'noticeboard' ) ?></label>
					<input 
						type="text" 
						name="announcement_link_text" 
						id="announcement_link_text" 
						value="<?= get_post_meta( $post->ID, 'announcement_link_text', true ); ?>" 
						placeholder="<?= __( 'Més info', 'noticeboard' ) ?>">
				</li>
			</ul>
		</div>

		<?php
	
	}

	function save_announcement_metabox_data( $post_id ) {
		/*
		 * We need to verify this came from our screen and with proper authorization,
		 * because the save_post action can be triggered at other times
		 */
	
		// Check if our nonce is set
		if ( ! isset( $_POST['nb_announcements_meta_box_nonce'] ) ) {
			return;
		}

		// Verify that the nonce is valid
		if ( ! wp_verify_nonce( $_POST['nb_announcements_meta_box_nonce'], 'nb_announcements_meta_box' ) ) {
			return;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		// Make sure that it is set
		if ( !isset( $_POST['announcement_summary'] ) || !isset( $_POST['announcement_link'] ) || !isset( $_POST['announcement_link_text'] ) ) {
			return;
		}
		
		/* Now it's safe to save the data */

		// Sanitize user input.
		$summary = sanitize_textarea_field( $_POST['announcement_summary']);
		$link = sanitize_url( $_POST['announcement_link'] );
		$link_text = sanitize_text_field( $_POST['announcement_link_text'] );

		// Update the meta field in the database
		update_post_meta( $post_id, 'announcement_summary', $summary );
		update_post_meta( $post_id, 'announcement_link', $link );
		update_post_meta( $post_id, 'announcement_link_text', $link_text );
	}


}
