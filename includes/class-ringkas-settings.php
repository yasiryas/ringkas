<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Opsi plugin + halaman Pengaturan di wp-admin.
 */
class Ringkas_Settings {

	public const OPTION_SITE_LABEL    = 'ringkas_site_label';
	public const OPTION_PUBLIC_ACCESS = 'ringkas_public_access';

	public static function register(): void {
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	/**
	 * Label situs untuk teks tampilan (login, dsb).
	 * Default: host domain, mis. "yasyes.id" → bisa diganti di Pengaturan.
	 */
	public static function site_label(): string {
		$label = sanitize_text_field( (string) get_option( self::OPTION_SITE_LABEL, '' ) );

		if ( '' !== $label ) {
			return $label;
		}

		return (string) wp_parse_url( home_url(), PHP_URL_HOST );
	}

	/**
	 * Bila aktif, dashboard /short/dashboard terbuka untuk siapa pun tanpa akun.
	 */
	public static function is_public(): bool {
		return (bool) get_option( self::OPTION_PUBLIC_ACCESS, false );
	}

	public static function register_settings(): void {
		register_setting(
			'ringkas_settings',
			self::OPTION_SITE_LABEL,
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		register_setting(
			'ringkas_settings',
			self::OPTION_PUBLIC_ACCESS,
			array(
				'type'              => 'boolean',
				'sanitize_callback' => array( __CLASS__, 'sanitize_bool' ),
				'default'           => false,
			)
		);

		add_settings_section(
			'ringkas_section_general',
			'Umum',
			'__return_false',
			'ringkas-settings'
		);

		add_settings_field(
			self::OPTION_SITE_LABEL,
			'Nama web / label',
			array( __CLASS__, 'render_site_label_field' ),
			'ringkas-settings',
			'ringkas_section_general',
			array( 'label_for' => self::OPTION_SITE_LABEL )
		);

		add_settings_field(
			self::OPTION_PUBLIC_ACCESS,
			'Akses tanpa login',
			array( __CLASS__, 'render_public_access_field' ),
			'ringkas-settings',
			'ringkas_section_general'
		);
	}

	public static function sanitize_bool( $value ): bool {
		return (bool) $value;
	}

	public static function render_site_label_field(): void {
		$value = esc_attr( self::site_label() );
		printf(
			'<input type="text" id="%1$s" name="%1$s" value="%2$s" class="regular-text">',
			esc_attr( self::OPTION_SITE_LABEL ),
			$value
		);
		echo '<p class="description">Dipakai pada halaman login dan teks tampilan. Kosongkan untuk memakai nama domain.</p>';
	}

	public static function render_public_access_field(): void {
		printf(
			'<label><input type="checkbox" name="%1$s" value="1" %2$s> Buka dashboard tanpa akun</label>',
			esc_attr( self::OPTION_PUBLIC_ACCESS ),
			checked( self::is_public(), true, false )
		);
		echo '<p class="description">Siapa pun bisa membuka <code>/short/dashboard</code> dan mengelola tautan tanpa login. Halaman login otomatis dilewati.</p>';
	}
}
