<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ringkas_title = 'Tautan Kedaluwarsa';
require RINGKAS_PLUGIN_DIR . 'templates/partials/head.php';
?>
<div class="auth-shell">
	<div class="auth-card auth-card-center">
		<div class="brand">Ringkas</div>
		<h1>Tautan sudah kedaluwarsa</h1>
		<p class="auth-sub">Tautan yang Anda buka sudah tidak aktif. Hubungi pihak yang membagikan tautan ini bila Anda merasa ada kesalahan.</p>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary">Ke halaman utama</a>
	</div>
</div>
<?php
require RINGKAS_PLUGIN_DIR . 'templates/partials/foot.php';
