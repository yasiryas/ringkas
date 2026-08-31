<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin options and settings page in wp-admin.
 */
class Yasyes_Shortlink_Settings {

	public const OPTION_SITE_LABEL    = 'yasyes_shortlink_site_label';
	public const OPTION_PUBLIC_ACCESS = 'yasyes_shortlink_public_access';

	public static function register(): void {
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	/**
	 * Site label for display text (login, etc.).
	 * Default: domain host. Can be changed in Settings.
	 */
	public static function site_label(): string {
		$label = sanitize_text_field( (string) get_option( self::OPTION_SITE_LABEL, '' ) );

		if ( '' !== $label ) {
			return $label;
		}

		return (string) wp_parse_url( home_url(), PHP_URL_HOST );
	}

	/**
	 * When active, the /short/dashboard is open to anyone without an account.
	 */
	public static function is_public(): bool {
		return (bool) get_option( self::OPTION_PUBLIC_ACCESS, false );
	}

	public static function register_settings(): void {
		register_setting(
			'yasyes_shortlink_settings',
			self::OPTION_SITE_LABEL,
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		register_setting(
			'yasyes_shortlink_settings',
			self::OPTION_PUBLIC_ACCESS,
			array(
				'type'              => 'boolean',
				'sanitize_callback' => array( __CLASS__, 'sanitize_bool' ),
				'default'           => false,
			)
		);

		add_settings_section(
			'yasyes_shortlink_section_general',
			'General',
			'__return_false',
			'yasyes-shortlink-settings'
		);

		add_settings_field(
			self::OPTION_SITE_LABEL,
			'Site name / label',
			array( __CLASS__, 'render_site_label_field' ),
			'yasyes-shortlink-settings',
			'yasyes_shortlink_section_general',
			array( 'label_for' => self::OPTION_SITE_LABEL )
		);

		add_settings_field(
			self::OPTION_PUBLIC_ACCESS,
			'Public access',
			array( __CLASS__, 'render_public_access_field' ),
			'yasyes-shortlink-settings',
			'yasyes_shortlink_section_general'
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
		echo '<p class="description">Used on the login page and display text. Leave empty to use the domain name.</p>';
	}

	public static function render_public_access_field(): void {
		printf(
			'<label><input type="checkbox" name="%1$s" value="1" %2$s> Open dashboard without login</label>',
			esc_attr( self::OPTION_PUBLIC_ACCESS ),
			checked( self::is_public(), true, false )
		);
		echo '<p class="description">Anyone can open <code>/short/dashboard</code> and manage links without logging in. The login page is automatically skipped.</p>';
	}
}
