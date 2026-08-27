<?php
/**
 * Halaman login custom di /short.
 *
 * @var string $error
 * @var string $notice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ringkas_title = 'Masuk — Ringkas';
require RINGKAS_PLUGIN_DIR . 'templates/partials/head.php';
?>
<div class="auth-shell">
	<div class="auth-card">
		<div class="brand">Ringkas</div>
		<h1>Masuk</h1>
		<p class="auth-sub">Kelola tautan pendek <?php echo esc_html( Ringkas_Settings::site_label() ); ?> dari satu tempat.</p>

		<?php if ( $notice ) : ?>
			<div class="alert alert-success"><?php echo esc_html( $notice ); ?></div>
		<?php endif; ?>
		<?php if ( $error ) : ?>
			<div class="alert alert-error"><?php echo esc_html( $error ); ?></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( home_url( '/short' ) ); ?>">
			<?php wp_nonce_field( 'ringkas_login' ); ?>
			<label class="field">
				<span>Username atau email</span>
				<input type="text" name="username" required autocomplete="username" autofocus>
			</label>
			<label class="field">
				<span>Password</span>
				<input type="password" name="password" required autocomplete="current-password">
			</label>
			<button type="submit" class="btn btn-primary btn-block">Masuk</button>
		</form>

		<p class="auth-alt"><a href="<?php echo esc_url( home_url( '/short/forgot-password' ) ); ?>">Lupa password?</a></p>
		<p class="auth-alt">Belum punya akun? <a href="<?php echo esc_url( home_url( '/short/register' ) ); ?>"><strong>Daftar</strong></a></p>
	</div>
</div>
<?php
require RINGKAS_PLUGIN_DIR . 'templates/partials/foot.php';
