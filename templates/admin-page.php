<?php
/**
 * Halaman "Kelola Tautan" di wp-admin (menu Ringkas).
 * Memakai bahasa visual yang sama dengan halaman Dokumentasi.
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
<div class="ringkas-body ringkas-admin">
	<div class="wrap ringkas-manage">
		<section class="doc-hero">
			<h1><span class="brand">Ringkas</span> &mdash; Kelola Tautan</h1>
			<p id="stats-line">
				<span id="stat-total"><?php echo esc_html( number_format_i18n( $stats['total_links'] ) ); ?></span> tautan &middot;
				<span id="stat-active"><?php echo esc_html( number_format_i18n( $stats['active'] ) ); ?></span> aktif &middot;
				<span id="stat-clicks"><?php echo esc_html( number_format_i18n( $stats['total_clicks'] ) ); ?></span> klik
				<span class="live-dot" id="live-dot" title="Diperbarui otomatis"></span>
			</p>
		</section>

		<section class="doc-section" id="links-card">
			<?php require RINGKAS_PLUGIN_DIR . 'templates/partials/app-main.php'; ?>
		</section>

		<p class="ringkas-footer-credit">
			<a href="https://buymeacoffee.com/yasir12398b" target="_blank" rel="noopener noreferrer">
				<i class="fa-solid fa-mug-hot" aria-hidden="true"></i> Buy me a coffee
			</a>
			&middot; dibuat oleh <a href="https://yasyes.id" target="_blank" rel="noopener noreferrer">Yasyes Studio</a>
			&middot; <button type="button" class="ringkas-footer-link" id="btn-feedback">
				<i class="fa-regular fa-comment-dots" aria-hidden="true"></i> Feedback
			</button>
		</p>
	</div>
</div>

<div class="modal-overlay" id="feedback-modal" hidden>
	<div class="modal modal-wide" role="dialog" aria-modal="true" aria-labelledby="feedback-title">
		<h2 id="feedback-title">Kirim Feedback</h2>
		<p class="muted">Saran, bug, atau permintaan fitur? Tulis di bawah ini.</p>
		<form id="feedback-form">
			<label class="field">
				<span>Pesan</span>
				<textarea name="message" rows="5" required minlength="10" maxlength="2000"
					placeholder="Tuliskan feedback Anda di sini..." id="feedback-message"></textarea>
			</label>
			<div class="modal-actions">
				<button type="button" class="btn" data-modal-close>Batal</button>
				<button type="submit" class="btn btn-primary">
					<i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Kirim
				</button>
			</div>
		</form>
	</div>
</div>
