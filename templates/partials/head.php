<?php
/**
 * @var string $ys_title Browser tab title.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex">
	<title><?php echo esc_html( $ys_title ?? 'Yasyes Short Link' ); ?></title>
	<?php wp_head(); ?>
</head>
<body class="ys-body">
