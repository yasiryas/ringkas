<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ringkas_title = 'Tautan Reset Tidak Valid';
require RINGKAS_PLUGIN_DIR . 'templates/partials/head.php';
?>
<div class="auth-shell">
	<div class="auth-card auth-card-center">
		<div class="brand">Ringkas</div>
		<h1>Tautan reset tidak valid</h1>
		<p class="auth-sub">Tautan reset password tidak valid atau sudah kedaluwarsa. Minta tautan baru untuk melanjutkan.</p>
		<a href="<?php echo esc_url( home_url( '/short/forgot-password' ) ); ?>" class="btn btn-primary">Minta tautan baru</a>
	</div>
</div>
<?php
require RINGKAS_PLUGIN_DIR . 'templates/partials/foot.php';
