<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ys_title = 'Invalid Reset Link';
require YASYES_SHORTLINK_PLUGIN_DIR . 'templates/partials/head.php';
?>
<div class="auth-shell">
	<div class="auth-card auth-card-center">
		<div class="brand">Yasyes Short Link</div>
		<h1>Invalid reset link</h1>
		<p class="auth-sub">The password reset link is invalid or has expired. Request a new link to continue.</p>
		<a href="<?php echo esc_url( home_url( '/short/forgot-password' ) ); ?>" class="btn btn-primary">Request new link</a>
	</div>
</div>
<?php
require YASYES_SHORTLINK_PLUGIN_DIR . 'templates/partials/foot.php';
