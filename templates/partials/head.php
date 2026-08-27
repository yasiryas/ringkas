<?php
/**
 * @var string $ringkas_title Judul tab browser.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex">
	<title><?php echo esc_html( $ringkas_title ?? 'Ringkas' ); ?></title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
	<link rel="stylesheet" href="<?php echo esc_url( RINGKAS_PLUGIN_URL . 'assets/ringkas.css' ); ?>?v=<?php echo esc_attr( RINGKAS_VERSION ); ?>">
</head>
<body class="ringkas-body">
