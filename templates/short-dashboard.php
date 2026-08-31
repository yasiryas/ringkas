<?php
/**
 * Dashboard Yasyes Short Link di /short/dashboard.
 * Render awal server-side; setelah itu list diperbarui via AJAX + polling.
 *
 * @var object[]      $links
 * @var int           $total
 * @var int           $total_pages
 * @var array         $stats       {total_links, total_clicks, active}
 * @var string        $search
 * @var int           $page
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user  = wp_get_current_user();
$ys_title = 'Dashboard — Yasyes Short Link';
$list_url      = home_url( '/short/dashboard' );
$logout_url    = wp_logout_url( home_url( '/short' ) );

require YASYES_SHORTLINK_PLUGIN_DIR . 'templates/partials/head.php';
?>
<header class="topbar">
	<span class="brand">Yasyes Short Link</span>
	<span class="muted" id="stats-line">
		<span id="stat-active"><?php echo esc_html( number_format_i18n( $stats['active'] ) ); ?></span> active &middot;
		<span id="stat-total"><?php echo esc_html( number_format_i18n( $stats['total_links'] ) ); ?></span> links &middot;
		<span id="stat-clicks"><?php echo esc_html( number_format_i18n( $stats['total_clicks'] ) ); ?></span> clicks
		<span class="live-dot" id="live-dot" title="Auto-refreshed"></span>
	</span>
	<span class="topbar-user">
		<?php if ( is_user_logged_in() ) : ?>
			<a href="<?php echo esc_url( admin_url( 'index.php' ) ); ?>"><span class="dashicons dashicons-admin-dashboard"></span> Dasbor WP</a>
			&middot;
			<?php echo esc_html( $current_user->display_name ?: $current_user->user_login ); ?>
			&middot;
			<a href="<?php echo esc_url( $logout_url ); ?>" id="logout-link">Logout</a>
		<?php else : ?>
			<span class="badge">public mode</span>
			&middot;
			<a href="<?php echo esc_url( home_url( '/short' ) ); ?>">Login</a>
		<?php endif; ?>
	</span>
</header>

<main class="wrap">
<section class="card">
<?php require YASYES_SHORTLINK_PLUGIN_DIR . 'templates/partials/app-main.php'; ?>
</section>

<p class="ys-footer-credit">
	<a href="https://buymeacoffee.com/yasir12398b" target="_blank" rel="noopener noreferrer">
		<span class="dashicons dashicons-coffee"></span> Buy me a coffee
	</a>
	&middot; made by <a href="https://yasyes.id" target="_blank" rel="noopener noreferrer">Yasyes Studio</a>
	&middot; <button type="button" class="ys-footer-link" id="btn-feedback">
		<span class="dashicons dashicons-admin-comments"></span> Feedback
	</button>
</p>
</main>

<div class="modal-overlay" id="feedback-modal" hidden>
	<div class="modal modal-wide" role="dialog" aria-modal="true" aria-labelledby="feedback-title">
		<h2 id="feedback-title">Send Feedback</h2>
		<p class="muted">Suggestions, bugs, or feature requests? Write them below.</p>
		<form id="feedback-form">
			<label class="field">
				<span>Message</span>
				<textarea name="message" rows="5" required minlength="10" maxlength="2000"
					placeholder="Write your feedback here..." id="feedback-message"></textarea>
			</label>
			<div class="modal-actions">
				<button type="button" class="btn" data-modal-close>Cancel</button>
				<button type="submit" class="btn btn-primary">
					<span class="dashicons dashicons-email-alt3"></span> Kirim
				</button>
			</div>
		</form>
	</div>
</div>

<div class="modal-overlay" id="logout-modal" hidden>
	<div class="modal modal-sm" role="dialog" aria-modal="true" aria-labelledby="logout-modal-title">
		<h2 id="logout-modal-title">Logout?</h2>
		<p class="muted">Your session will end. Continue to Logout?</p>
		<div class="modal-actions">
			<button type="button" class="btn" id="logout-cancel">Cancel</button>
			<a class="btn btn-primary" href="<?php echo esc_url( $logout_url ); ?>" id="logout-confirm">Yes, logout</a>
		</div>
	</div>
</div>
<?php
require YASYES_SHORTLINK_PLUGIN_DIR . 'templates/partials/foot.php';
