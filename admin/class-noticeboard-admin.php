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
		add_action( 'admin_init', array( $this, 'register_and_build_fields' ));
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
			'name' 					=> __( 'Anuncis', 'noticeboard' ),
			'singular_name' 		=> __( 'Anunci', 'noticeboard' ),
			'add_new' 				=> __( 'Afegir anunci', 'noticeboard' ),
			'add_new_item' 			=> __( 'Afegir nou anunci', 'noticeboard' ),
			'edit_item' 			=> __( 'Editar anunci', 'noticeboard' ),
			'new_item' 				=> __( 'Nou anunci', 'noticeboard' ),
			'view_item' 			=> __( 'Veure anunci', 'noticeboard' ),
			'search_items' 			=> __( 'Cercar anunci', 'noticeboard' ),
			'not_found' 			=> __( 'No s\'han trobat anuncis', 'noticeboard' ),
			'not_found_in_trash'	=> __( 'No s\'han trobat anuncis a la paperera', 'noticeboard' ),
		);
		$args = array(
			'labels' 		=> $labels, 									// Specific labels for this post type, e.g. Plugin name
			'public' 		=> true, 										// Whether or not this post type is exposed to the public
			'show_in_menu'	=> $this->plugin_name, 							// Where to show this post type in the admin menu
			'supports' 		=> array( 'title', 'editor', 'custom_fields'),	// Features this post type supports
			'has_archive' 	=> false, 										// We don't need an archive since we build our own templates using The Loop
			'menu_icon' 	=> 'dashicons-megaphone', 						// Icon to use for this post type in the admin menu
			'rewrite'     	=> array(									
				'slug' 		=> _x( 'anuncis', 'slug', 'noticeboard' )			// Custom slug (URL) for this post type
			),
			'show_in_rest' 	=> true, 										// Allow Gutenberg editor to use this post type
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
			$this->plugin_name, 
			__( 'Taulell d\'anuncis', 'noticeboard' ),
			'administrator',
			$this->plugin_name,
			array( $this, 'display_plugin_admin_dashboard' ),
			'dashicons-megaphone',
		);
		
		add_submenu_page( 
			$this->plugin_name, 
			__( 'Opcions del Taulell d\'anuncis', 'noticeboard' ),
			__( 'Opcions', 'noticeboard' ), 
			'administrator', 
			$this->plugin_name . '-settings', 
			array( $this, 'display_plugin_admin_settings' ),
		);

	}

	/**
	 * Display the admin dashboard
	 * 
	 * @since 1.0.0
	 */
	public function display_plugin_admin_dashboard() {
    	require_once 'partials/' . $this->plugin_name . '-admin-display.php';
	}

	/**
	 * Display the admin settings page
	 * 
	 * @since 1.0.0
	 */
	public function display_plugin_admin_settings() {
        $active_tab = $_GET[ 'tab' ] ?? 'general';
        if ( isset( $_GET['error_message'] ) ) {
            add_action( 'admin_notices', array( $this, 'admin_settings_messages' ) );
            do_action( 'admin_notices', $_GET['error_message'] );
        }
        require_once 'partials/' . $this->plugin_name . '-admin-settings-display.php';
	}

	/**
	 * Display the admin settings messages
	 * 
	 * @since 1.0.0
	 */
	public function admin_settings_messages( $error_message ) {
         switch ( $error_message ) {
             case '1':
				$message = __( 'S\'ha produït un error al guardar les opcions. Si us plau, intenta-ho de nou. Si el problema continua, posa\'t amb l\'administrador del lloc . ', 'noticeboard' );
				$err_code = esc_attr( 'noticeboard_nextcloud_url_setting' );
				$setting_field = 'noticeboard_nextcloud_url_setting';
				break;
			case '2':
				$message = __( 'S\'ha produït un error al guardar les opcions. Si us plau, intenta-ho de nou. Si el problema continua, posa\'t amb l\'administrador del lloc . ', 'noticeboard' );
				$err_code = esc_attr( 'noticeboard_nextcloud_user_setting' );                 
				$setting_field = 'noticeboard_nextcloud_user_setting';                 
				break;
			case '3':
				$message = __( 'S\'ha produït un error al guardar les opcions. Si us plau, intenta-ho de nou. Si el problema continua, posa\'t amb l\'administrador del lloc . ', 'noticeboard' );
				$err_code = esc_attr( 'noticeboard_nextcloud_password_setting' );                 
				$setting_field = 'noticeboard_nextcloud_password_setting';                 
				break;
        }
        $type = 'error';
        add_settings_error(
			$setting_field,
			$err_code,
			$message,
			$type
        );
    }

	/**
	 * Register the settings for the plugin
	 * 
	 * @since 1.0.0
	 */
	public function register_and_build_fields() {

		/**
		 * Nextcloud settings section
		 */
		add_settings_section(
			// ID used to identify this section and with which to register options
			'noticeboard_nextcloud_section',
			// Title to be displayed on the administration page
			'',  
			// Callback used to render the description of the section
			array( $this, 'noticeboard_display_nextcloud_description' ),    
			// Page on which to add this section of options
			'noticeboard_nextcloud_settings'                   
		);

		// Nextcloud URL
		unset( $args );
		$args = array (
			'type'      => 'input',
			'subtype'   => 'url',
			'id'    => 'noticeboard_nextcloud_url_setting',
			'name'      => 'noticeboard_nextcloud_url_setting',
			'required' => false,
			'get_options_list' => '',
			'value_type' => 'normal',
			'wp_data' => 'option'
		);
		add_settings_field(
			'noticeboard_nextcloud_url_setting',
			__( 'URL de Nextcloud', 'noticeboard' ),
			array( $this, 'plugin_name_render_settings_field' ),
			'noticeboard_nextcloud_settings',
			'noticeboard_nextcloud_section',
			$args
		);
		register_setting(
			'noticeboard_nextcloud_settings',
			'noticeboard_nextcloud_url_setting'
		);

		// Nextcloud user
		unset($args);
		$args = array (
			'type'      => 'input',
			'subtype'   => 'text',
			'id'    => 'noticeboard_nextcloud_user_setting',
			'name'      => 'noticeboard_nextcloud_user_setting',
			'required' => false,
			'get_options_list' => '',
			'value_type' => 'normal',
			'wp_data' => 'option'
		);
		add_settings_field(
			'noticeboard_nextcloud_user_setting',
			__( 'Usuari de Nextcloud', 'noticeboard' ),
			array( $this, 'plugin_name_render_settings_field' ),
			'noticeboard_nextcloud_settings',
			'noticeboard_nextcloud_section',
			$args
		);
		register_setting(
			'noticeboard_nextcloud_settings',
			'noticeboard_nextcloud_user_setting'
		);

		// Nextcloud password
		unset($args);
		$args = array (
			'type'      => 'input',
			'subtype'   => 'password',
			'id'    => 'noticeboard_nextcloud_password_setting',
			'name'      => 'noticeboard_nextcloud_password_setting',
			'required' => false,
			'get_options_list' => '',
			'value_type' => 'normal',
			'wp_data' => 'option'
		);
		add_settings_field(
			'noticeboard_nextcloud_password_setting',
			__( 'Contrasenya de Nextcloud', 'noticeboard' ),
			array( $this, 'plugin_name_render_settings_field' ),
			'noticeboard_nextcloud_settings',
			'noticeboard_nextcloud_section',
			$args
		);
		register_setting(
			'noticeboard_nextcloud_settings',
			'noticeboard_nextcloud_password_setting'
		);

	}

	/**
	 * Display the Nextcloud description
	 * 
	 * @since 1.0.0
	 */
	function noticeboard_display_nextcloud_description() {
		echo '<p>' . __( 'Aquestes opcions permeten ajustar els paràmetres que es fan servir amb l\'API de Nextcloud', 'noticeboard' ) . '</p>';
	}

	/**
	 * Render the settings field
	 * 
	 * @since 1.0.0 
	 * @param array $args Arguments for the field
	 */
	public function plugin_name_render_settings_field( $args ) {
		if ($args['wp_data'] == 'option') {
				$wp_data_value = get_option( $args['name'] );
			} elseif ( $args['wp_data'] == 'post_meta' ) {
				$wp_data_value = get_post_meta( $args['post_id'], $args['name'], true );
			}

			switch ( $args['type'] ) {

				case 'input':
					$value =  ( $args['value_type'] ==  'serialized') ? serialize( $wp_data_value ) : $wp_data_value;
					 if ( $args['subtype'] != 'checkbox') {
						$prependStart = ( isset( $args['prepend_value'] ) ) ? '<div class="input-prepend"> <span class="add-on">' . $args['prepend_value'] . '</span>' : '';
						$prependEnd = ( isset( $args['prepend_value'] ) ) ? '</div>' : '';
						$step = ( isset( $args['step'] ) ) ? 'step="' . $args['step'] . '"' : '';
						$min = ( isset( $args['min'] ) ) ? 'min="' . $args['min'] . '"' : '';
						$max = ( isset( $args['max'] ) ) ? 'max="' . $args['max'] . '"' : '';
						if ( isset( $args['disabled'] ) ) {
							// hide the actual input bc if it was just a disabled input the information saved in the database would be wrong - bc it would pass empty values and wipe the actual information
							echo $prependStart . '<input type="' . $args['subtype'] . '" id="' . $args['id'] . '_disabled" ' . $step . ' ' . $max . ' ' . $min . ' name="' . $args['name'] . '_disabled" size="40" disabled value="' . esc_attr($value) . '" /><input type="hidden" id="' . $args['id'] . '" ' . $step . ' ' . $max . ' ' . $min . ' name="' . $args['name'] . '" size="40" value="' . esc_attr($value) . '" />' . $prependEnd;
						} else {
							echo $prependStart . '<input type="' . $args['subtype'] . '" id="' . $args['id'] . '" ' . $args['required'] . ' ' . $step . ' ' . $max . ' ' . $min . ' name="' . $args['name'] . '" size="40" value="' . esc_attr($value) . '" />' . $prependEnd;
						}
						/*<input required="required" ' . $disabled . ' type="number" step="any" id="' . $this->plugin_name . '_cost2" name="' . $this->plugin_name . '_cost2" value="' . esc_attr( $cost ) . '" size="25" /><input type="hidden" id="' . $this->plugin_name . '_cost" step="any" name="' . $this->plugin_name . '_cost" value="' . esc_attr( $cost ) . '" />*/

					} else {
						$checked = ($value) ? 'checked' : '';
						echo '<input type="' . $args['subtype'] . '" id="' . $args['id'] . '" "' . $args['required'] . '" name="' . $args['name'] . '" size="40" value="1" ' . $checked . ' />';
					}
					break;
				default:
					break;
			}
		}

	public function setup_announcement_metaboxes() {
		add_meta_box(
			'ng_announcement_metaboxes', 
			__( 'Camps personalitzats pels anuncis', 'noticeboard' ), 
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
						value="<?= get_post_meta( $post->ID, 'announcement_link_text', true ) ?: __( 'Més info', 'noticeboard' ) ?>" 
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
