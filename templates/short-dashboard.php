<?php
/**
 * Dashboard Ringkas di /short/dashboard.
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
$ringkas_title = 'Dashboard — Ringkas';
$list_url      = home_url( '/short/dashboard' );
$logout_url    = wp_logout_url( home_url( '/short' ) );

require RINGKAS_PLUGIN_DIR . 'templates/partials/head.php';
?>
<header class="topbar">
	<span class="brand">Ringkas</span>
	<span class="muted" id="stats-line">
		<span id="stat-active"><?php echo esc_html( number_format_i18n( $stats['active'] ) ); ?></span> aktif &middot;
		<span id="stat-total"><?php echo esc_html( number_format_i18n( $stats['total_links'] ) ); ?></span> tautan &middot;
		<span id="stat-clicks"><?php echo esc_html( number_format_i18n( $stats['total_clicks'] ) ); ?></span> klik
		<span class="live-dot" id="live-dot" title="Diperbarui otomatis"></span>
	</span>
	<span class="topbar-user">
		<?php if ( is_user_logged_in() ) : ?>
			<a href="<?php echo esc_url( admin_url( 'index.php' ) ); ?>"><i class="fa-solid fa-gauge-high" aria-hidden="true"></i> Dasbor WP</a>
			&middot;
			<?php echo esc_html( $current_user->display_name ?: $current_user->user_login ); ?>
			&middot;
			<a href="<?php echo esc_url( $logout_url ); ?>" id="logout-link">Keluar</a>
		<?php else : ?>
			<span class="badge">mode terbuka</span>
			&middot;
			<a href="<?php echo esc_url( home_url( '/short' ) ); ?>">Masuk</a>
		<?php endif; ?>
	</span>
</header>

<main class="wrap">
<section class="card">
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
</main>

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

<div class="modal-overlay" id="logout-modal" hidden>
	<div class="modal modal-sm" role="dialog" aria-modal="true" aria-labelledby="logout-modal-title">
		<h2 id="logout-modal-title">Keluar?</h2>
		<p class="muted">Sesi Anda akan diakhiri. Lanjutkan keluar?</p>
		<div class="modal-actions">
			<button type="button" class="btn" id="logout-cancel">Batal</button>
			<a class="btn btn-primary" href="<?php echo esc_url( $logout_url ); ?>" id="logout-confirm">Ya, keluar</a>
		</div>
	</div>
</div>
<?php
require RINGKAS_PLUGIN_DIR . 'templates/partials/foot.php';
