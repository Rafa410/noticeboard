<?php

/**
 * Provide a admin area view for the plugin
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
	<h2><?= __( 'Taulell d\'anuncis', 'noticeboard' ) ?></h2>

	<div class="card w-100 mw-100">
		<h3 class="card-title"><?= __( 'ℹ️ Informació', 'noticeboard' ) ?></h3>
		<p class="card-text">
			<?= __( 'Pots afegir el taulell d\'anuncis a qualsevol pàgina amb el shortcode', 'noticeboard' ) ?>
			<code>[latest_announcements]</code>.
		</p>
		<p class="card-text mb-2">
			<?= __( 'Per defecte, es mostren tots els anuncis, tant de la web com de l\'extranet. Hi ha disponibles alguns paràmetres per filtrar els anuncis, per exemple: ', 'noticeboard' ) ?>
		</p>
		<ul class="card-text small">
			<li>
				<?= __( 'Per mostrar només els últims 5 anuncis: ', 'noticeboard' ) ?>
				<code>[latest_announcements limit=5]</code>
			</li>
			<li>
				<?= __( 'Per mostrar només els anuncis creats al web: ', 'noticeboard' ) ?>
				<code>[latest_announcements source=web]</code>
			</li>
			<li>
				<?= __( 'Per mostrar només els anuncis creats a la extranet: ', 'noticeboard' ) ?>
				<code>[latest_announcements source=extranet]</code>
			</li>
			<li>
				<?= __( 'Per mostrar només els anuncis creats a la extranet i limitar-los a 10: ', 'noticeboard' ) ?>
				<code>[latest_announcements source=extranet limit=10]</code>
			</li>
		</ul>
	</div>

	<div class="row">
		<div class="col-md-6">
			
			<div class="card">
				<h3 class="card-title"><?= __( '⚙️ Configuració', 'noticeboard' ) ?></h3>
				<p class="card-text">
					<?= __( 'Per configurar el taulell d\'anuncis, accedeix a la pàgina de', 'noticeboard' ) ?>
					<a href="<?= admin_url( 'admin.php?page=noticeboard-settings' ) ?>">
						<b><?= __( 'Configuració', 'noticeboard' ) ?></b>
					</a>.
				</p>
			</div>

		</div>

		<div class="col-md-6">

			<div class="card">
				<h3 class="card-title"><?= __( '📢 Anuncis', 'noticeboard' ) ?></h3>
				<p class="card-text">
					<?= __( 'Per publicar un nou anunci, accedeix a la pàgina de', 'noticeboard' ) ?>
					<a href="<?= admin_url( 'edit.php?post_type=nb_announcements' ) ?>">
						<b><?= __( 'Anuncis', 'noticeboard' ) ?></b>
					</a>.
				</p>
			</div>

		</div>
	</div>

</div>
