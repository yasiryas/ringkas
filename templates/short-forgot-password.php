<?php
/**
 * @var string $message
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ringkas_title = 'Lupa Password — Ringkas';
require RINGKAS_PLUGIN_DIR . 'templates/partials/head.php';
?>
<div class="auth-shell">
	<div class="auth-card">
		<div class="brand">Ringkas</div>
		<h1>Lupa password</h1>
		<p class="auth-sub">Masukkan email akun Anda. Tautan reset akan dikirim lewat email.</p>

		<?php if ( $message ) : ?>
			<div class="alert alert-success"><?php echo esc_html( $message ); ?></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( home_url( '/short/forgot-password' ) ); ?>">
			<?php wp_nonce_field( 'ringkas_forgot' ); ?>
			<label class="field">
				<span>Email</span>
				<input type="email" name="email" required autocomplete="email" autofocus>
			</label>
			<button type="submit" class="btn btn-primary btn-block">Kirim tautan reset</button>
		</form>

		<p class="auth-alt"><a href="<?php echo esc_url( home_url( '/short' ) ); ?>">Kembali ke halaman masuk</a></p>
	</div>
</div>
<?php
require RINGKAS_PLUGIN_DIR . 'templates/partials/foot.php';
