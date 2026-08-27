<?php
/**
 * @var string $error
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ringkas_title = 'Daftar — Ringkas';
require RINGKAS_PLUGIN_DIR . 'templates/partials/head.php';
?>
<div class="auth-shell">
	<div class="auth-card">
		<div class="brand">Ringkas</div>
		<h1>Buat akun</h1>
		<p class="auth-sub">Akun memberi Anda akses ke dashboard tautan.</p>

		<?php if ( $error ) : ?>
			<div class="alert alert-error"><?php echo esc_html( $error ); ?></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( home_url( '/short/register' ) ); ?>">
			<?php wp_nonce_field( 'ringkas_register' ); ?>
			<label class="field">
				<span>Username</span>
				<input type="text" name="username" required autocomplete="username" minlength="3" autofocus>
			</label>
			<label class="field">
				<span>Email</span>
				<input type="email" name="email" required autocomplete="email">
			</label>
			<label class="field">
				<span>Password (min. 8 karakter)</span>
				<input type="password" name="password" required autocomplete="new-password" minlength="8">
			</label>
			<button type="submit" class="btn btn-primary btn-block">Daftar</button>
		</form>

		<p class="auth-alt">Sudah punya akun? <a href="<?php echo esc_url( home_url( '/short' ) ); ?>"><strong>Masuk</strong></a></p>
	</div>
</div>
<?php
require RINGKAS_PLUGIN_DIR . 'templates/partials/foot.php';
