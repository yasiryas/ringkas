<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menu Ringkas di wp-admin — UI manajemen yang sama dengan /short/dashboard.
 */
class Ringkas_Admin_Menu {

	public const PAGE_SLUG = 'ringkas';

	private const FA_URL = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css';

	public static function register(): void {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	public static function add_menu(): void {
		add_menu_page(
			'Ringkas',
			'Ringkas',
			'manage_ringkas_links',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' ),
			'dashicons-admin-links',
			26
		);

		add_submenu_page(
			self::PAGE_SLUG,
			'Ringkas',
			'Kelola Tautan',
			'manage_ringkas_links',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' )
		);

		add_submenu_page(
			self::PAGE_SLUG,
			'Pengaturan Ringkas',
			'Pengaturan',
			'manage_ringkas_links',
			'ringkas-settings',
			array( __CLASS__, 'render_settings_page' )
		);

		add_submenu_page(
			self::PAGE_SLUG,
			'Dokumentasi Ringkas',
			'Dokumentasi',
			'manage_ringkas_links',
			'ringkas-docs',
			array( __CLASS__, 'render_docs_page' )
		);
	}

	public static function enqueue_assets( string $hook ): void {
		$is_menu_hook    = 'toplevel_page_' . self::PAGE_SLUG === $hook;
		$is_submenu_hook = false !== strpos( $hook, '_page_' . self::PAGE_SLUG );

		if ( ! $is_menu_hook && ! $is_submenu_hook ) {
			return;
		}		wp_enqueue_style( 'ringkas-font-awesome', self::FA_URL, array(), '6.7.2' );
		wp_enqueue_style(
			'ringkas-app',
			RINGKAS_PLUGIN_URL . 'assets/ringkas.css',
			array( 'ringkas-font-awesome' ),
			RINGKAS_VERSION
		);

		wp_enqueue_script( 'ringkas-app', RINGKAS_PLUGIN_URL . 'assets/ringkas.js', array(), RINGKAS_VERSION, true );
		wp_add_inline_script(
			'ringkas-app',
			sprintf(
				'window.RingkasConfig = { ajaxUrl: %s, nonce: %s, pollMs: 30000 };',
				wp_json_encode( admin_url( 'admin-ajax.php' ) ),
				wp_json_encode( wp_create_nonce( 'ringkas_ajax' ) )
			),
			'before'
		);
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_ringkas_links' ) ) {
			wp_die( 'Anda tidak punya akses.' );
		}

		$result      = Ringkas_Link_Model::paginate( '', 1 );
		$links       = $result['items'];
		$total       = $result['total'];
		$total_pages = (int) ceil( $total / Ringkas_Link_Model::PER_PAGE );
		$stats       = Ringkas_Link_Model::stats();
		$search      = '';
		$page        = 1;
		$list_url    = menu_page_url( self::PAGE_SLUG, false );

		include RINGKAS_PLUGIN_DIR . 'templates/admin-page.php';
	}


	public static function render_settings_page(): void {
		if ( ! current_user_can( 'manage_ringkas_links' ) ) {
			wp_die( 'Anda tidak punya akses.' );
		}
		?>
		<div class="wrap">
			<h1>Pengaturan Ringkas</h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'ringkas_settings' );
				do_settings_sections( 'ringkas-settings' );
				submit_button( 'Simpan Pengaturan' );
				?>
			</form>
		</div>
		<?php
	}

	public static function render_docs_page(): void {
		if ( ! current_user_can( 'manage_ringkas_links' ) ) {
			wp_die( 'Anda tidak punya akses.' );
		}

		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		?>
		<div class="wrap ringkas-docs">
			<section class="doc-hero">
				<h1><span class="brand">Ringkas</span> &mdash; Dokumentasi</h1>
				<p>Ubah URL panjang menjadi tautan pendek di domain Anda sendiri:
				<code><?php echo esc_html( $host ); ?>/promo</code> &rarr; halaman tujuan.</p>
			</section>

			<section class="doc-section">
				<h2>Mulai dalam 3 langkah</h2>
				<ol class="doc-steps">
					<li>
						<span class="step-num">1</span>
						<div>
							<strong>Buka Kelola Tautan</strong>
							<p>Menu <em>Ringkas &rarr; Kelola Tautan</em>, klik tombol <em>Buat tautan</em>.</p>
						</div>
					</li>
					<li>
						<span class="step-num">2</span>
						<div>
							<strong>Isi URL tujuan</strong>
							<p>Alias dan kedaluwarsa opsional. Klik <em>Simpan</em>.</p>
						</div>
					</li>
					<li>
						<span class="step-num">3</span>
						<div>
							<strong>Bagikan</strong>
							<p>Klik ikon salin, lalu sebarkan tautan pendeknya. Klik terhitung otomatis.</p>
						</div>
					</li>
				</ol>
			</section>

			<section class="doc-section">
				<h2>Aturan singkat</h2>
				<div class="doc-grid">
					<div class="doc-card">
						<h3><i class="fa-solid fa-tag" aria-hidden="true"></i> Alias</h3>
						<p>3&ndash;20 huruf/angka tanpa spasi. Harus unik dan tidak boleh bentrok
						dengan slug halaman/post yang sudah ada. Kosongkan = dibuat acak otomatis.</p>
					</div>
					<div class="doc-card">
						<h3><i class="fa-regular fa-clock" aria-hidden="true"></i> Kedaluwarsa</h3>
						<p>Bila diisi, setelah waktunya lewat pengunjung melihat halaman
						&ldquo;tautan kedaluwarsa&rdquo; (HTTP 410). Kosong = berlaku selamanya.</p>
					</div>
					<div class="doc-card">
						<h3><i class="fa-solid fa-chart-line" aria-hidden="true"></i> Statistik klik</h3>
						<p>Setiap kunjungan melalui short link dicatat per tautan dan ditampilkan
						pada kolom <em>Klik</em>, plus total di bagian atas dashboard.</p>
					</div>
					<div class="doc-card">
						<h3><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Prioritas aman</h3>
						<p>Jika alias bentrok dengan konten WordPress yang ada, halaman asli WordPress
						yang menang &mdash; short link tidak pernah merusak situs.</p>
					</div>
				</div>
			</section>

			<section class="doc-section">
				<h2>Aksi pada daftar tautan</h2>
				<table class="doc-table">
					<thead>
						<tr><th>Ikon</th><th>Aksi</th><th>Keterangan</th></tr>
					</thead>
					<tbody>
						<tr>
							<td><i class="fa-regular fa-copy" aria-hidden="true"></i></td>
							<td>Salin</td>
							<td>Salin short link ke clipboard.</td>
						</tr>
						<tr>
							<td><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></td>
							<td>Edit</td>
							<td>Ubah tujuan, alias, atau waktu kedaluwarsa via modal.</td>
						</tr>
						<tr>
							<td><i class="fa-solid fa-trash-can" aria-hidden="true"></i></td>
							<td>Hapus</td>
							<td>Terdapat modal konfirmasi; terhapus permanen.</td>
						</tr>
					</tbody>
				</table>
				<p class="doc-note">Pencarian bekerja langsung saat mengetik (alias &amp; URL tujuan),
				daftar dibatasi 20/halaman, dan data disegarkan otomatis tiap 5 detik tanpa reload.</p>
			</section>

			<section class="doc-section">
				<h2>Halaman publik</h2>
				<table class="doc-table">
					<thead>
						<tr><th>Alamat</th><th>Fungsi</th></tr>
					</thead>
					<tbody>
						<tr><td><code>/short</code></td><td>Login dashboard</td></tr>
						<tr><td><code>/short/register</code></td><td>Daftar akun baru (otomatis dapat akses Ringkas)</td></tr>
						<tr><td><code>/short/forgot-password</code></td><td>Minta tautan reset password via email</td></tr>
						<tr><td><code>/short/dashboard</code></td><td>Dashboard front-end (di luar wp-admin)</td></tr>
					</tbody>
				</table>
				<p class="doc-note">Akses dikelola lewat kapabilitas <code>manage_ringkas_links</code>.
				Semua pendaftar halaman publik otomatis mendapatkannya; administrator selalu akses penuh.</p>
			</section>

			<section class="doc-section">
				<h2>Pertanyaan cepat</h2>
				<div class="doc-grid">
					<div class="doc-card">
						<h3>Ganti nama web di halaman login?</h3>
						<p>Menu <em>Ringkas &rarr; Pengaturan</em>. Kosongkan untuk memakai nama domain.</p>
					</div>
					<div class="doc-card">
						<h3>Short link mati setelah edit alias?</h3>
						<p>Ya. Alias lama langsung tidak berlaku &mdash; pastikan tautan lama sudah tidak tersebar.</p>
					</div>
					<div class="doc-card">
						<h3>Bisa tanpa login?</h3>
						<p>Aktifkan <em>Akses tanpa login</em> di menu Pengaturan &mdash;
						halaman login dilewati dan dashboard terbuka untuk siapa pun.</p>
					</div>
				</div>
			</section>
		</div>
		<?php
	}
}
