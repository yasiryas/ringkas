<?php
/**
 * @var WP_User $user
 * @var string  $error
 * @var string  $key
 * @var string  $login
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ringkas_title = 'Reset Password — Ringkas';
require RINGKAS_PLUGIN_DIR . 'templates/partials/head.php';
?>
<div class="auth-shell">
	<div class="auth-card">
		<div class="brand">Ringkas</div>
		<h1>Password baru</h1>
		<p class="auth-sub">Atur password baru untuk akun <strong><?php echo esc_html( $user->user_login ); ?></strong>.</p>

		<?php if ( $error ) : ?>
			<div class="alert alert-error"><?php echo esc_html( $error ); ?></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( home_url( '/short/reset-password' ) ); ?>">
			<?php wp_nonce_field( 'ringkas_reset' ); ?>
			<input type="hidden" name="key" value="<?php echo esc_attr( $key ); ?>">
			<input type="hidden" name="login" value="<?php echo esc_attr( $login ); ?>">
			<label class="field">
				<span>Password baru (min. 8 karakter)</span>
				<input type="password" name="password" required autocomplete="new-password" minlength="8" autofocus>
			</label>
			<button type="submit" class="btn btn-primary btn-block">Simpan password</button>
		</form>

	</div>
</div>
<?php
require RINGKAS_PLUGIN_DIR . 'templates/partials/foot.php';
