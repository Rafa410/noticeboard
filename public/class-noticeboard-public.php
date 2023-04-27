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
	public function register_shortcodes() {
		add_shortcode( 'latest_announcements', array( $this, 'shortcode_announcements_handler' ) );
	}

	/**
	 * Generates the content of the latest announcements shortcode
	 * 
	 * @param array $atts Shortcode attributes
	 * 
	 * @return string
	 */
	public function shortcode_announcements_handler( $atts ) {

		$atts = shortcode_atts( array(
			'limit' => -1, // Maximum number of announcements to show
			'source' => '', // 'web' OR 'extranet'
		), $atts, 'latest_announcements' );

		if ( $atts['source'] === 'extranet' ) {
			$output = $this->get_nextcloud_announcements( $atts['limit'] );
		} elseif ( $atts['source'] === 'web' ) {
			$output = $this->get_wp_announcements( $atts['limit'] );
		} else {
			$output = $this->get_combined_announcements( $atts['limit'] );
		}

		return $output;
	}

	/**
	 * Get WP announcements
	 * 
	 * @param int $limit Number of announcements to get
	 * 
	 * @return (string|WP_Post[])
	 */
	public function get_wp_announcements( $limit = -1, $generate_html = true ) {
		$args = array(
			'post_type' => 'nb_announcements',
			'posts_per_page' => $limit,
			'orderby' => 'date',
			'order' => 'DESC',
		);

		$query = new WP_Query( $args );

		$output = '';
		
		if ( $query->have_posts() ) {
			$parsed_announcements = array_map( function( $post ) {
				return $post->to_array();
			}, $query->posts );

			if ( $generate_html ) {
				$output .= '<div class="announcements-list p-3">';
				foreach ( $parsed_announcements as $announcement ) {
					$output .=   $this->generate_announcement_html( $announcement );
				}
				$output .= '</div>';

			} else {
				$output = $parsed_announcements;
			}
			
		} elseif ( $generate_html ) {
			$output .= '<small class="d-block text-center fw-light">' . __( 'No s\'han trobat anuncis recents', 'noticeboard' ) . '</small>';
		} else {
			$output = array();
		}

		return $output;
	}

	/**
	 * Get Nextcloud announcements from REST API
	 *
	 * @param int $limit Number of announcements to get
	 *
	 * @todo filter by group 'API'
	 * groups: [
	 *	{
	 *   id: "API",
	 *	 name: "Web"
	 *	 }
	 *	],
	 *
	 * @return (string|Array)
	 */
	public function get_nextcloud_announcements( $limit, $generate_html = true, $api_format = 'json' ) {

		// Reset limit
		if ( $limit === -1 ) {
			$limit = null;
		}

		// Load Slimdown Markdown parser
		$this->slimdown_init();
 
		// Get API params from plugin settings page
		$base_url = get_option( 'noticeboard_nextcloud_url_setting' );
		$user = get_option( 'noticeboard_nextcloud_user_setting' );
		$password = get_option( 'noticeboard_nextcloud_password_setting' );
		$max_age = get_option( 'noticeboard_nextcloud_sync_frequency_setting' ) ?? DAY_IN_SECONDS;
		
		// The group ID to filter by
		$group_ids = ['API'];

		// Build the URL
		$url = trailingslashit( $base_url ) . 'ocs/v2.php/apps/announcementcenter/api/v1/announcements';
		$query_params = array (
			'format' => $api_format,
		);
		$query_url = $url . '?' . http_build_query($query_params);

		// Set the transient key
		$transient_key = 'nc::announcementcenter_data';

		// Check if the data is already in the cache
		if ( false === ( $response = get_transient( $transient_key ) ) ) {

			// If not, get the data from the remote API
			$args = array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( $user . ':' . $password ),
					'OCS-APIRequest' => true,
					'Cache-Control' => 'max-age=' . $max_age,
				),
			);

			// Add the If-Modified-Since header if the transient exists
			$last_modified = get_transient( $transient_key . '_last_modified' );
			if ( $last_modified ) {
				$args['headers']['If-Modified-Since'] = $last_modified;
			}

			$response = wp_remote_get( $query_url, $args );

			// Check if the response was successful
			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {

				// If the response was successful, cache the data
				$response_body = wp_remote_retrieve_body( $response );
				set_transient( $transient_key, $response_body, $max_age );
				
				// Set the last modified header for future requests
				$last_modified_header = wp_remote_retrieve_header( $response, 'last-modified' );
				if ( $last_modified_header ) {
					set_transient( $transient_key . '_last_modified', $last_modified_header, $max_age );
				}

			}
		} else {
			// If the data is in the cache, use it
			$response_body = $response;
		}

		if ( ! $response || ! $response_body ) {
			// Handle the error
			if ( $generate_html ) {
				return '<p class="text-danger">' . __( 'No s\'han pogut obtenir els anuncis', 'noticeboard' ) . '</p>';
			}
			return [];
		}

		// Process the announcements
		if ( $api_format === 'json' ) {
			$json = json_decode( $response_body, true );
			$data = $json['ocs']['data'];

			// Filter by group ID
			$announcements = array_filter( $data, function( $announcement ) use ( $group_ids ) {
				$groups = $announcement['groups'];
				foreach ( $groups as $group ) {
					if ( in_array( $group['id'], $group_ids ) ) {
						return true;
					}
				}
				return false;
			} );

			$total_announcements = count( $announcements );
		} elseif ( $api_format === 'xml' ) {
			$xml = simplexml_load_string( $response_body, 'SimpleXMLElement', LIBXML_NOCDATA );

			// Build the XPath expression to filter by one or more group IDs
			$xpath_expression = "//element[";
			foreach ($group_ids as $group_id) {
				$xpath_expression .= "groups/element/id='{$group_id}' or ";
			}
			// Replace the last ' or ' with a closing bracket
			$xpath_expression = rtrim($xpath_expression, " or ") . "]";
			
			$xml_filtered = $xml->xpath($xpath_expression);

			$announcements = json_decode( json_encode( ( array ) $xml_filtered ), true );
			$total_announcements = count( $announcements );
		}

		if ( ! isset($total_announcements) || $total_announcements < 1 ) {
			if ( $generate_html ) {
				return '<p>' . __( 'No s\'han trobat anuncis', 'noticeboard' ) . '</p>';
			} else {
				return [];
			}
		}

		$limit = $limit ?? $total_announcements;

		if ( $generate_html ) {
			$output = '<div class="announcements-list p-3">';
		}

		for ( $i = 0; $i < $limit; $i++ ) {
			$announcement = $announcements[$i];
			$parsed_announcements = $this->parse_nc_announcement_to_wp_post( $announcement );

			if ( $generate_html ) {
				$output .= $this->generate_announcement_html( $parsed_announcements );
			} else {
				$output[] = $parsed_announcements;
			}
		}

		if ( $generate_html ) {
			$output .= '</div>';
		}

		return $output;
	}

	/**
	 * Get combined announcements from both Nextcloud and WordPress
	 * @param int $limit Number of announcements to get
	 */
	public function get_combined_announcements( $limit ) {
		$nc_announcements = $this->get_nextcloud_announcements(null, false);
		$wp_announcements = $this->get_wp_announcements(-1, false);

		$announcements = $this->merge_announcements( array($wp_announcements, $nc_announcements), $limit );

		$output = '<div class="announcements-list p-3">';

		foreach ( $announcements as $announcement ) {
			$output .= $this->generate_announcement_html( $announcement );
		}

		$output .= '</div>';

		return $output;
	}

	/**
	 * Convert an announcement from NC API to use WP Post structure
	 * @param SimpleXMLElement $announcement
	 * @return array
	 */
	public function parse_nc_announcement_to_wp_post ( $announcement ) {
		$excerpt_markdown = substr($announcement['message'], 0, 165); // Limit excerpt to 165 characters
		$excerpt_markdown = substr($excerpt_markdown, 0, strrpos($excerpt_markdown, ' ')) . '...'; // Avoid breaking last word

		$post = array(
			'ID' => $announcement['id'],
			'post_author' => $announcement['author_id'],
			'post_date' => date('Y-m-d H:i:s', (string) $announcement['time'] ),
			'post_content' => $announcement['message'],
			'post_title' => $announcement['subject'],
			'post_excerpt' => $excerpt_markdown,
			'post_status' => 'publish',
			'post_type' => 'nc::announcements',
		);

		return $post;
	}

	/**
	 * Merge announcements from different sources
	 * @param array $announcements_arr Array of array of announcements to merge
	 * @param int $limit Number of announcements to get
	 */
	public function merge_announcements( $announcements_arr, $limit) {
		$merged = array();

		foreach ( $announcements_arr as $i => $announcements ) {
			$merged = array_merge($merged, $announcements);
		}

		if ( $limit > 0 ) {
			$merged = array_slice($merged, 0, $limit);
		}

		usort($merged, function($a, $b) {
			return strtotime($b['post_date']) - strtotime($a['post_date']);
		});

		return $merged;
	}

	/**
	 * Generate HTML for an announcement
	 * @param array $announcement Announcement to generate HTML for
	 * @return string HTML for announcement
	 */
	public function generate_announcement_html( $announcement ) {
		if ( $announcement['post_type'] === 'nb_announcements' ) {
			return $this->generate_wp_announcement_html( $announcement );
		} elseif ( $announcement['post_type'] === 'nc::announcements' ) {
			return $this->generate_nc_announcement_html( $announcement );
		}
	}

	public function generate_wp_announcement_html ( $announcement ) {
		$permalink = get_post_meta( $announcement['ID'], 'announcement_link', true ) ?: get_permalink( $announcement['ID'] );
		$permalink_text = get_post_meta( $announcement['ID'], 'announcement_link_text', true );
		$excerpt = get_post_meta( $announcement['ID'], 'announcement_summary', true );

		if ( ! $excerpt ) {
			$excerpt = $announcement['post_excerpt'];
		}

		ob_start();
		?>

		<article class="announcement announcement--wp" id="announcement-<?= $announcement['ID'] ?>">

			<header class="entry-header">
				<h4 class="entry-title fs-6">
					<a class="link-dark text-decoration-none fw-bold" href="<?= esc_url( $permalink ) ?>">
						<?= $announcement['post_title']; ?>
					</a>
				</h4>
			</header>

			<div class="entry-content">
				<?= $excerpt ?>
			</div>

			<?php if ( $permalink_text ) : ?>
				<footer class="entry-footer mt-2">
					<a href="<?= esc_url( $permalink ) ?>" class="read-more btn btn-sm btn-outline-dark fw-bold">
						<?= get_post_meta( $announcement['ID'], 'announcement_link_text', true ) ?>
					</a>
				</footer>
			<?php endif; ?>

		</article>

		<?php

		return ob_get_clean();

	}

	/**
	 * Generate HTML for an announcement from Nextcloud
	 * @param array $announcement Announcement to generate HTML for
	 * @return string HTML for announcement
	 */
	public function generate_nc_announcement_html ( $announcement ) {
		$excerpt_markdown = substr($announcement['post_excerpt'], 0, 165); // Limit excerpt to 165 characters
		$excerpt_markdown = substr($excerpt_markdown, 0, strrpos($excerpt_markdown, ' ')) . '...'; // Avoid breaking last word
		$excerpt_html = Slimdown::render( $excerpt_markdown );

		ob_start();
		?>

		<article class="announcement announcement--nc" id="announcement-<?= $announcement['ID'] ?>">

			<header class="entry-header">
				<h4 class="entry-title fs-6 fw-bold">
					<?= $announcement['post_title']; ?>
				</h4>
			</header>

			<div class="entry-content">
				<?= $excerpt_html ?>
			</div>

		</article>

		<?php

		return ob_get_clean();

	}

}
