<?php

/**
 * Provide a admin area view for the plugin's settings page
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/Rafa410/
 * @since      1.0.0
 *
 * @package    Noticeboard
 * @subpackage Noticeboard/admin/partials
 */
?>

<div class="wrap">
	<h2><?= __( 'Opcions del taulell d\'anuncis', 'noticeboard' ) ?></h2>
	<?php settings_errors(); ?>
	<form method="POST" action="options.php" class="card">
		<h3><?= __( 'Paràmetres de Nextcloud', 'noticeboard' ) ?></h3>
		<?php
			settings_fields( 'noticeboard_nextcloud_settings' );
			do_settings_sections( 'noticeboard_nextcloud_settings' );
		?>
		<?php submit_button(); ?>
	</form>
</div>