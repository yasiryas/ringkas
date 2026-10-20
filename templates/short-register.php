<?php
/**
 * Custom register page at /short/register.
 *
 * @var string $error
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ys_title = 'Register — Yasyes Short Link';
require YASYES_SHORTLINK_PLUGIN_DIR . 'templates/partials/head.php';
?>
<div class="auth-shell">
	<div class="auth-card">
		<div class="brand">Yasyes Short Link</div>
		<h1>Create account</h1>
		<p class="auth-sub">Register an account to manage short links for <?php echo esc_html( Yasyes_Shortlink_Settings::site_label() ); ?>.</p>

		<?php if ( $error ) : ?>
			<div class="alert alert-error"><?php echo esc_html( $error ); ?></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( home_url( '/short/register' ) ); ?>">
			<?php wp_nonce_field( 'yasyes_shortlink_register' ); ?>
			<label class="field">
				<span>Username</span>
				<input type="text" name="username" required autocomplete="username" autofocus>
			</label>
			<label class="field">
				<span>Email</span>
				<input type="email" name="email" required autocomplete="email">
			</label>
			<label class="field">
				<span>Password</span>
				<input type="password" name="password" required autocomplete="new-password" minlength="8">
			</label>
			<button type="submit" class="btn btn-primary btn-block">Register</button>
		</form>

		<p class="auth-alt"><a href="<?php echo esc_url( home_url( '/short' ) ); ?>">Back to login</a></p>
	</div>
</div>
<?php
require YASYES_SHORTLINK_PLUGIN_DIR . 'templates/partials/foot.php';
