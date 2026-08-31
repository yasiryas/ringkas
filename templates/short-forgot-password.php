<?php
/**
 * @var string $message
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ys_title = 'Forgot Password — Yasyes Short Link';
require YASYES_SHORTLINK_PLUGIN_DIR . 'templates/partials/head.php';
?>
<div class="auth-shell">
	<div class="auth-card">
		<div class="brand">Yasyes Short Link</div>
		<h1>Forgot password</h1>
		<p class="auth-sub">Enter your account email. A reset link will be sent to your inbox.</p>

		<?php if ( $message ) : ?>
			<div class="alert alert-success"><?php echo esc_html( $message ); ?></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( home_url( '/short/forgot-password' ) ); ?>">
			<?php wp_nonce_field( 'yasyes_shortlink_forgot' ); ?>
			<label class="field">
				<span>Email</span>
				<input type="email" name="email" required autocomplete="email" autofocus>
			</label>
			<button type="submit" class="btn btn-primary btn-block">Kirim links reset</button>
		</form>

		<p class="auth-alt"><a href="<?php echo esc_url( home_url( '/short' ) ); ?>">Back to login</a></p>
	</div>
</div>
<?php
require YASYES_SHORTLINK_PLUGIN_DIR . 'templates/partials/foot.php';
