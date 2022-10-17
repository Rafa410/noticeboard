<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/Rafa410/
 * @since      1.0.0
 *
 * @package    Noticeboard
 * @subpackage Noticeboard/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Noticeboard
 * @subpackage Noticeboard/public
 * @author     Rafa Soler <rafasoler10@gmail.com>
 */
class Noticeboard_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/noticeboard-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/noticeboard-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Include Slimdown.php library to parse markdown
	 */
	public function slimdown_init() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/Slimdown/Slimdown.php';
	}

	/**
	 * Register shortcodes
	 *
	 * @since    1.0.0
	 */
	function register_shortcodes() {
		add_shortcode( 'latest_announcements', array( $this, 'shortcode_announcements_handler' ) );
	}

	/**
	 * Generates the content of the latest announcements shortcode
	 * 
	 * @param array $atts Shortcode attributes
	 * 
	 * @return string
	 */
	function shortcode_announcements_handler( $atts ) {

		$atts = shortcode_atts( array(
			'limit' => 4, // Maximum number of announcements to show
			'source' => 'wp', // Or 'nextcloud'
		), $atts, 'latest_announcements' );

		if ( $atts['source'] === 'nextcloud' ) {
			$output = $this->get_nextcloud_announcements( $atts['limit'] );
		} else {
			$output = $this->get_wp_announcements( $atts['limit'] );
		}

		return $output;
	}

	/**
	 * Get WP announcements
	 * 
	 * @param int $limit Number of announcements to get
	 * 
	 * @return string
	 */
	function get_wp_announcements( $limit ) {
		$args = array(
			'post_type' => 'nb_announcements',
			'posts_per_page' => $limit,
			'orderby' => 'date',
			'order' => 'DESC',
		);

		$query = new WP_Query( $args );

		$output = '<div class="announcements-list p-3">';

		if ( $query->have_posts() ) {

			while ( $query->have_posts() ) {
				$query->the_post();

				$permalink = get_post_meta( get_the_ID(), 'announcement_link', true ) ?: get_permalink();
				$permalink_text = get_post_meta( get_the_ID(), 'announcement_link_text', true );
				$excerpt = get_post_meta( get_the_ID(), 'announcement_summary', true );

				if ( ! $excerpt ) {
					$excerpt = get_the_excerpt();
					$excerpt = substr($excerpt, 0, 165); // Limit excerpt to 165 characters
					$excerpt = substr($excerpt, 0, strrpos($excerpt, ' ')) . '...'; // Avoid breaking last word
				}

				ob_start();
				?>

				<article class="announcement" id="announcement-<?php the_ID(); ?>">

					<header class="entry-header">
						<h4 class="entry-title fs-6">
							<a class="link-dark text-decoration-none fw-bold" href="<?= esc_url( $permalink ) ?>">
								<?= get_the_title(); ?>
							</a>
						</h4>
					</header>

					<div class="entry-content">
						<?= $excerpt ?>
					</div>

					<?php if ( $permalink_text ) : ?>
						<footer class="entry-footer mt-2">
							<a href="<?= esc_url( $permalink ) ?>" class="read-more btn btn-sm btn-outline-dark fw-bold">
								<?= get_post_meta( get_the_ID(), 'announcement_link_text', true ) ?>
							</a>
						</footer>
					<?php endif; ?>

				</article>

				<?php
				
				$output .= ob_get_clean();

			}

		} else {
			$output .= '<small class="d-block text-center fw-light">' . __( 'No s\'han trobat anuncis recents', 'noticeboard' ) . '</small>';
		}

		$output .= '</div>';

		wp_reset_postdata();

		return $output;
	}

	/**
	 * Get Nextcloud announcements from REST API
	 * 
	 * @param int $limit Number of announcements to get
	 * 
	 * @return string
	 */
	function get_nextcloud_announcements( $limit ) {

		// Load Slimdown Markdown parser
		$this->slimdown_init();
 
		// Get API params from plugin settings page
		$base_url = get_option( 'noticeboard_nextcloud_url_setting' );
		$user = get_option( 'noticeboard_nextcloud_user_setting' );
		$password = get_option( 'noticeboard_nextcloud_password_setting' );
		$url = trailingslashit( $base_url ) . 'ocs/v2.php/apps/announcementcenter/api/v1/announcements';

		$args = array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $user . ':' . $password ),
				'OCS-APIRequest' => true,
			),
		);

		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			return '<p class="text-danger">' . $response->get_error_message() . '</p>';
		}

		$body = $response['body'];

		$xml = simplexml_load_string( $body );

		// echo '<pre>' . print_r($xml, true) . '</pre>'; // Debug

		if ( $xml === false ) {
			return '<p>' . __( 'No s\'han trobat anuncis', 'noticeboard' ) . '</p>';
		}

		$output = '<div class="announcements-list p-3">';


		/** Example XML response from API  **/
		// SimpleXMLElement Object
		// (
		// 	[meta] => SimpleXMLElement Object
		// 		(
		// 			[status] => ok
		// 			[statuscode] => 200
		// 			[message] => OK
		// 		)
		// 	[data] => SimpleXMLElement Object
		// 		(
		// 			[element] => SimpleXMLElement Object
		// 				(
		// 					[id] => 5
		// 					[author_id] => superadmin
		// 					[author] => superadmin
		// 					[time] => 1650843974
		// 					[subject] => Lorem ipsum
		// 					[message] => Lorem ipsum dolor sit amet, consectetur adipiscing elit.
		// 					[groups] => SimpleXMLElement Object
		// 						(
		// 							[element] => SimpleXMLElement Object
		// 								(
		// 									[id] => everyone
		// 									[name] => everyone
		// 								)
		// 						)
		// 					[comments] => 0
		// 					[notifications] => 1
		// 				)
		// 		)
		// )

		foreach ( $xml->data->element as $announcement ) {
			$excerpt_markdown = substr($announcement->message, 0, 165); // Limit excerpt to 165 characters
			$excerpt_markdown = substr($excerpt_markdown, 0, strrpos($excerpt_markdown, ' ')) . '...'; // Avoid breaking last word
			$excerpt_html = Slimdown::render( $excerpt_markdown ); // TODO: Use wp_kses() to sanitize output

			$content = Slimdown::render( $announcement->message );

			ob_start();
			?>

			<article class="announcement" id="announcement-<?= $announcement->id ?>">

				<header class="entry-header">
					<h4 class="entry-title fs-6 fw-bold">
						<?= $announcement->subject; ?>
					</h4>
				</header>

				<div class="entry-content">
					<?= $excerpt_html ?>
				</div>

			</article>

			<?php
			
			$output .= ob_get_clean();

		}

		$output .= '</div>';

		return $output;

	}		

}
