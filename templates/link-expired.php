<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ys_title = 'Link Expired';
require YASYES_SHORTLINK_PLUGIN_DIR . 'templates/partials/head.php';
?>
<div class="auth-shell">
	<div class="auth-card auth-card-center">
		<div class="brand">Yasyes Short Link</div>
		<h1>Link has expired</h1>
		<p class="auth-sub">The link you opened is no longer active. Contact the person who shared it if you believe this is an error.</p>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary">Go to homepage</a>
	</div>
</div>
<?php
require YASYES_SHORTLINK_PLUGIN_DIR . 'templates/partials/foot.php';
