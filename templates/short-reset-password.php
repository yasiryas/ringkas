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

$ys_title = 'Reset Password — Yasyes Short Link';
require YASYES_SHORTLINK_PLUGIN_DIR . 'templates/partials/head.php';
?>
<div class="auth-shell">
	<div class="auth-card">
		<div class="brand">Yasyes Short Link</div>
		<h1>New password</h1>
		<p class="auth-sub">Set a new password for account <strong><?php echo esc_html( $user->user_login ); ?></strong>.</p>

		<?php if ( $error ) : ?>
			<div class="alert alert-error"><?php echo esc_html( $error ); ?></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( home_url( '/short/reset-password' ) ); ?>">
			<?php wp_nonce_field( 'yasyes_shortlink_reset' ); ?>
			<input type="hidden" name="key" value="<?php echo esc_attr( $key ); ?>">
			<input type="hidden" name="login" value="<?php echo esc_attr( $login ); ?>">
			<label class="field">
				<span>New password (min. 8 characters)</span>
				<input type="password" name="password" required autocomplete="new-password" minlength="8" autofocus>
			</label>
			<button type="submit" class="btn btn-primary btn-block">Save password</button>
		</form>

	</div>
</div>
<?php
require YASYES_SHORTLINK_PLUGIN_DIR . 'templates/partials/foot.php';
