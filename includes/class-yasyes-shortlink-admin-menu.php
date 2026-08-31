<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Yasyes Short Link menu in wp-admin — same management UI as /short/dashboard.
 */
class Yasyes_Shortlink_Admin_Menu {

	public const PAGE_SLUG = 'yasyes-shortlink';

	public static function register(): void {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	public static function add_menu(): void {
		add_menu_page(
			'yasyes-shortlink',
			'yasyes-shortlink',
			'manage_options',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' ),
			'dashicons-admin-links',
			26
		);

		add_submenu_page(
			self::PAGE_SLUG,
			'yasyes-shortlink',
			'Manage Links',
			'manage_options',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' )
		);

		add_submenu_page(
			self::PAGE_SLUG,
			'Settings Yasyes Short Link',
			'Settings',
			'manage_options',
			'yasyes-shortlink-settings',
			array( __CLASS__, 'render_settings_page' )
		);

		add_submenu_page(
			self::PAGE_SLUG,
			'Documentation Yasyes Short Link',
			'Documentation',
			'manage_options',
			'ys-docs',
			array( __CLASS__, 'render_docs_page' )
		);
	}

	public static function enqueue_assets( string $hook ): void {
		$is_menu_hook    = 'toplevel_page_' . self::PAGE_SLUG === $hook;
		$is_submenu_hook = false !== strpos( $hook, '_page_' . self::PAGE_SLUG );

		if ( ! $is_menu_hook && ! $is_submenu_hook ) {
			return;
		}

		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style(
			'ys-app',
			YASYES_SHORTLINK_PLUGIN_URL . 'assets/yasyes-shortlink.css',
			array( 'dashicons' ),
			YASYES_SHORTLINK_VERSION
		);

		wp_enqueue_script( 'ys-app', YASYES_SHORTLINK_PLUGIN_URL . 'assets/yasyes-shortlink.js', array(), YASYES_SHORTLINK_VERSION, true );
		wp_add_inline_script(
			'ys-app',
			sprintf(
				'window.YasyesShortlinkConfig = { ajaxUrl: %s, nonce: %s, pollMs: 30000 };',
				wp_json_encode( admin_url( 'admin-ajax.php' ) ),
				wp_json_encode( wp_create_nonce( 'yasyes_shortlink_ajax' ) )
			),
			'before'
		);
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have access.' );
		}

		$result      = Yasyes_Shortlink_Link_Model::paginate( '', 1 );
		$links       = $result['items'];
		$total       = $result['total'];
		$total_pages = (int) ceil( $total / Yasyes_Shortlink_Link_Model::PER_PAGE );
		$stats       = Yasyes_Shortlink_Link_Model::stats();
		$search      = '';
		$page        = 1;
		$list_url    = menu_page_url( self::PAGE_SLUG, false );

		include YASYES_SHORTLINK_PLUGIN_DIR . 'templates/admin-page.php';
	}


	public static function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have access.' );
		}
		?>
		<div class="wrap">
			<h1>Yasyes Short Link Settings</h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'yasyes_shortlink_settings' );
				do_settings_sections( 'yasyes-shortlink-settings' );
				submit_button( 'Save Settings' );
				?>
			</form>
		</div>
		<?php
	}

	public static function render_docs_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have access.' );
		}

		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		?>
		<div class="wrap ys-docs">
			<section class="doc-hero">
				<h1><span class="brand">Yasyes Short Link</span> &mdash; Dokumentasi</h1>
				<p>Turn long URLs into short links on your own domain:
				<code><?php echo esc_html( $host ); ?>/promo</code> &rarr; destination page.</p>
			</section>

			<section class="doc-section">
				<h2>Get started in 3 steps</h2>
				<ol class="doc-steps">
					<li>
						<span class="step-num">1</span>
						<div>
							<strong>Open Manage Links</strong>
							<p>Menu <em>Yasyes Short Link &rarr; Manage Links</em>, click the <em>Create Link</em> button.</p>
						</div>
					</li>
					<li>
						<span class="step-num">2</span>
						<div>
							<strong>Enter the destination URL</strong>
							<p>Alias and expiry are optional. Click <em>Save</em>.</p>
						</div>
					</li>
					<li>
						<span class="step-num">3</span>
						<div>
							<strong>Share</strong>
							<p>Click the copy icon, then share the short link. Clicks are counted automatically.</p>
						</div>
					</li>
				</ol>
			</section>

			<section class="doc-section">
				<h2>Quick rules</h2>
				<div class="doc-grid">
					<div class="doc-card">
						<h3><span class="dashicons dashicons-tag" aria-hidden="true"></span> Alias</h3>
						<p>3&ndash;20 alphanumeric characters, no spaces. Must be unique and must not conflict
						with existing page/post slugs. Leave empty = auto-generated.</p>
					</div>
					<div class="doc-card">
						<h3><span class="dashicons dashicons-clock" aria-hidden="true"></span> Expires</h3>
						<p>When set, visitors will see a
						&ldquo;link expired&rdquo; page (HTTP 410). Empty = valid forever.</p>
					</div>
					<div class="doc-card">
						<h3><span class="dashicons dashicons-chart-bar" aria-hidden="true"></span> Click statistics</h3>
						<p>Each visit through a short link is tracked per link and displayed
						in the <em>Clicks</em> column, plus totals at the top of the dashboard.</p>
					</div>
					<div class="doc-card">
						<h3><span class="dashicons dashicons-shield" aria-hidden="true"></span> Safety priority</h3>
						<p>If an alias conflicts with existing WordPress content, the original WordPress page
						wins &mdash; short links never break your site.</p>
					</div>
				</div>
			</section>

			<section class="doc-section">
				<h2>Actions on the link list</h2>
				<table class="doc-table">
					<thead>
						<tr><th>Icon</th><th>Action</th><th>Description</th></tr>
					</thead>
					<tbody>
						<tr>
							<td><span class="dashicons dashicons-admin-page" aria-hidden="true"></i></td>
							<td>Copy</td>
							<td>Copy short link to clipboard.</td>
						</tr>
						<tr>
							<td><span class="dashicons dashicons-edit" aria-hidden="true"></i></td>
							<td>Edit</td>
							<td>Change destination, alias, or expiry via modal.</td>
						</tr>
						<tr>
							<td><span class="dashicons dashicons-trash" aria-hidden="true"></i></td>
							<td>Delete</td>
							<td>Confirmation modal; permanently deleted.</td>
						</tr>
					</tbody>
				</table>
				<p class="doc-note">Search works as you type (alias &amp; destination URL),
				list is limited to 20/page, and data refreshes automatically every 5 seconds without reload.</p>
			</section>

			<section class="doc-section">
				<h2>Public pages</h2>
				<table class="doc-table">
					<thead>
						<tr><th>URL</th><th>Purpose</th></tr>
					</thead>
					<tbody>
						<tr><td><code>/short</code></td><td>Dashboard login</td></tr>
						<tr><td><code>/short/forgot-password</code></td><td>Request password reset via email</td></tr>
						<tr><td><code>/short/dashboard</code></td><td>Front-end dashboard (outside wp-admin)</td></tr>
					</tbody>
				</table>
				<p class="doc-note">Access is managed via the <code>manage_options</code> capability.
				Public pages are accessible to everyone; administrators always have full access.</p>
			</section>

			<section class="doc-section">
				<h2>Quick questions</h2>
				<div class="doc-grid">
					<div class="doc-card">
						<h3>Change the site name on the login page??</h3>
						<p>Menu <em>Yasyes Short Link &rarr; Settings</em>. Leave empty to use the domain name.</p>
					</div>
					<div class="doc-card">
						<h3>Short link stops working after editing the alias??</h3>
						<p>Yes. The old alias is immediately invalid &mdash; make sure the old link is no longer shared.</p>
					</div>
					<div class="doc-card">
						<h3>Can I use it without logging in??</h3>
						<p>Enable <em>Public access</em> in the Settings menu &mdash;
						the login page is skipped and the dashboard is open to everyone.</p>
					</div>
				</div>
			</section>
		</div>
		<?php
	}
}
