<?php
defined( 'ABSPATH' ) || exit; ?>

<div class="wrap wpex-demo-import-not-supported">

	<h1><?php esc_html_e( 'Demo Importer', 'total-theme-core' ); ?></h1>

	<?php
	// Get errors
	$errors = $this->init_checks;

	// Loop through errors
	foreach ( $errors as $error ) : ?>

		<div class="notice notice-error"><p><?php echo $error; ?></p></div>

	<?php endforeach; ?>

</div>