<?php

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/Rafa410/
 * @since      1.0.0
 *
 * @package    Noticeboard
 * @subpackage Noticeboard/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Noticeboard
 * @subpackage Noticeboard/includes
 * @author     Rafa Soler <rafasoler10@gmail.com>
 */
class Noticeboard_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// Clear the permalinks after the registering custom post types.
    	flush_rewrite_rules();
	}

}
