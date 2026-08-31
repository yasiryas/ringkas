<?php
/**
 * "Manage Links" page in wp-admin (Yasyes Short Link menu).
 * Uses the same visual language as the Documentation page.
 *
 * @var object[]    $links
 * @var int         $total
 * @var int         $total_pages
 * @var array       $stats       {total_links, total_clicks, active}
 * @var string      $search
 * @var int         $page
 * @var object|null $editing
 * @var string      $error
 * @var string      $list_url
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="ys-body ys-admin">
	<div class="wrap ys-manage">
		<section class="doc-hero">
			<h1><span class="brand">Yasyes Short Link</span> &mdash; Manage Links</h1>
			<p id="stats-line">
				<span id="stat-total"><?php echo esc_html( number_format_i18n( $stats['total_links'] ) ); ?></span> links &middot;
				<span id="stat-active"><?php echo esc_html( number_format_i18n( $stats['active'] ) ); ?></span> active &middot;
				<span id="stat-clicks"><?php echo esc_html( number_format_i18n( $stats['total_clicks'] ) ); ?></span> clicks
				<span class="live-dot" id="live-dot" title="Auto-refreshed"></span>
			</p>
		</section>

		<section class="doc-section" id="links-card">
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
	</div>
</div>

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
