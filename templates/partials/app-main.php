<?php
/**
 * Konten utama aplikasi: toolbar + tabel + pagination + modal (form & hapus).
 * Dipakai di dashboard front-end (/short/dashboard) dan menu wp-admin.
 *
 * @var object[]    $links       Tautan di halaman ini.
 * @var int         $total       Total hasil.
 * @var int         $total_pages Jumlah halaman.
 * @var array       $stats       {total_links, total_clicks, active}
 * @var string      $search      Kata kunci pencarian awal.
 * @var int         $page        Halaman aktif.
 * @var string      $list_url    URL dasar daftar tautan.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	<div class="toolbar">
			<form method="get" class="search-bar" action="<?php echo esc_url( $list_url ); ?>" id="search-form">
				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" id="search-input"
					placeholder="Cari alias atau URL tujuan&hellip;" autocomplete="off">
			</form>
			<button type="button" class="btn btn-primary" id="btn-create">
				<i class="fa-solid fa-plus" aria-hidden="true"></i> Buat tautan
			</button>
		</div>

		<div class="table-wrap" id="table-wrap">
			<table class="link-table">
				<thead>
					<tr>
						<th>Tautan</th>
						<th>URL tujuan</th>
						<th class="col-num">Klik</th>
						<th>Kedaluwarsa</th>
						<th class="col-actions">Aksi</th>
					</tr>
				</thead>
				<tbody id="link-tbody">
					<?php foreach ( $links as $link ) : ?>
						<?php
						$expired   = Ringkas_Link_Model::is_expired( $link );
						$short_url = home_url( '/' . $link->short_code );
						?>
						<tr data-id="<?php echo esc_attr( $link->id ); ?>">
							<td>
								<a class="short-code" href="<?php echo esc_url( $short_url ); ?>" target="_blank" rel="noopener">/<?php echo esc_html( $link->short_code ); ?></a>
								<?php if ( $expired ) : ?><span class="badge badge-danger">kedaluwarsa</span><?php endif; ?>
							</td>
							<td><span class="target-url" title="<?php echo esc_attr( $link->original_url ); ?>"><?php echo esc_html( $link->original_url ); ?></span></td>
							<td class="col-num"><?php echo esc_html( number_format_i18n( (int) $link->click_count ) ); ?></td>
							<td><?php echo $link->expired_at ? esc_html( date_i18n( 'd M Y H:i', strtotime( $link->expired_at ) ) ) : '&mdash;'; ?></td>
							<td class="col-actions">
								<button type="button" class="btn-icon" data-copy="<?php echo esc_attr( $short_url ); ?>" title="Salin tautan"><i class="fa-regular fa-copy" aria-hidden="true"></i></button>
								<button type="button" class="btn-icon" data-edit="<?php echo esc_attr( $link->id ); ?>" title="Edit"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></button>
								<button type="button" class="btn-icon btn-icon-danger" data-delete="<?php echo esc_attr( $link->id ); ?>" title="Hapus"><i class="fa-solid fa-trash-can" aria-hidden="true"></i></button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ( empty( $links ) ) : ?>
				<p class="muted empty" id="empty-state">
					<?php echo '' !== $search ? 'Tidak ada hasil untuk pencarian ini.' : 'Belum ada tautan. Klik "Buat tautan" untuk memulai.'; ?>
				</p>
			<?php endif; ?>
		</div>

		<nav class="pagination" id="pagination" aria-label="Navigasi halaman"<?php echo $total_pages <= 1 ? ' hidden' : ''; ?>></nav>

<div class="modal-overlay" id="link-modal" hidden>
	<div class="modal modal-wide" role="dialog" aria-modal="true" aria-labelledby="form-title">
		<h2 id="form-title">Buat tautan</h2>
		<form method="post" action="<?php echo esc_url( $list_url ); ?>" id="link-form">
			<?php wp_nonce_field( 'ringkas_link_save' ); ?>
			<input type="hidden" name="action_type" value="create">

			<label class="field">
				<span>URL tujuan</span>
				<input type="url" name="original_url" required placeholder="https://contoh.com/halaman-panjang" id="form-url">
			</label>

			<label class="field">
				<span>Alias <em class="optional">(opsional saat buat)</em></span>
				<input type="text" name="alias" pattern="[A-Za-z0-9]{3,20}" autocomplete="off"
					placeholder="promo2026" id="form-alias">
				<small class="hint">3&ndash;20 huruf/angka. Kosongkan untuk dibuat otomatis.</small>
			</label>

			<label class="field">
				<span>Kedaluwarsa <em class="optional">(opsional)</em></span>
				<input type="datetime-local" name="expired_at">
			</label>

			<button type="submit" class="btn btn-primary btn-block">Simpan</button>
		</form>
	</div>
</div>

<div class="modal-overlay" id="delete-modal" hidden>
	<div class="modal" role="dialog" aria-modal="true" aria-labelledby="delete-title">
		<h2 id="delete-title">Hapus tautan?</h2>
		<p class="muted">Tautan /<strong id="delete-code"></strong> akan dihapus permanen.</p>
		<div class="modal-actions">
			<button type="button" class="btn" id="delete-cancel">Batal</button>
			<button type="button" class="btn btn-danger" id="delete-confirm">
				<i class="fa-solid fa-trash-can" aria-hidden="true"></i> Ya, hapus
			</button>
		</div>
	</div>
</div>
