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
			'limit' => 4,
		), $atts, 'latest_announcements' );

		$args = array(
			'post_type' => 'nb_announcements',
			'posts_per_page' => $atts['limit'],
			'orderby' => 'date',
			'order' => 'DESC',
		);

		$query = new WP_Query( $args );

		$output = '<div class="announcements-list p-3">';

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
						<a class="text-decoration-none fw-bold" href="<?= esc_url( $permalink ) ?>">
							<?= get_the_title(); ?>
						</a>
					</h4>
				</header>

				<div class="entry-content">
					<?= $excerpt ?>
				</div>

				<?php if ( $permalink_text ) : ?>
					<footer class="entry-footer mt-2 text-end">
						<a href="<?= esc_url( $permalink ) ?>" class="btn btn-sm btn-secondary link-primary fw-bold">
							<?= get_post_meta( get_the_ID(), 'announcement_link_text', true ) ?>
						</a>
					</footer>
				<?php endif; ?>

			</article>

			<?php
			
			$output .= ob_get_clean();

		}

		$output .= '</div>';

		wp_reset_postdata();

		return $output;
	}

}
