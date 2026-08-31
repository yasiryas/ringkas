<?php
/**
 * Custom login page at /short.
 *
 * @var string $error
 * @var string $notice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ys_title = 'Login — Yasyes Short Link';
require YASYES_SHORTLINK_PLUGIN_DIR . 'templates/partials/head.php';
?>
<div class="auth-shell">
	<div class="auth-card">
		<div class="brand">Yasyes Short Link</div>
		<h1>Login</h1>
		<p class="auth-sub">Manage short links for <?php echo esc_html( Yasyes_Shortlink_Settings::site_label() ); ?> from one place.</p>

		<?php if ( $notice ) : ?>
			<div class="alert alert-success"><?php echo esc_html( $notice ); ?></div>
		<?php endif; ?>
		<?php if ( $error ) : ?>
			<div class="alert alert-error"><?php echo esc_html( $error ); ?></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( home_url( '/short' ) ); ?>">
			<?php wp_nonce_field( 'yasyes_shortlink_login' ); ?>
			<label class="field">
				<span>Username or email</span>
				<input type="text" name="username" required autocomplete="username" autofocus>
			</label>
			<label class="field">
				<span>Password</span>
				<input type="password" name="password" required autocomplete="current-password">
			</label>
			<button type="submit" class="btn btn-primary btn-block">Login</button>
		</form>

		<p class="auth-alt"><a href="<?php echo esc_url( home_url( '/short/forgot-password' ) ); ?>">Forgot password?</a></p>
		<p class="auth-alt">Need an account? Contact your administrator.</p>
	</div>
</div>
<?php
require YASYES_SHORTLINK_PLUGIN_DIR . 'templates/partials/foot.php';
