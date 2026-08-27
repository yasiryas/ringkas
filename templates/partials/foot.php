<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="toast" id="ringkas-toast" role="status" aria-live="polite"></div>
<script>
window.RingkasConfig = {
	ajaxUrl: <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,
	nonce: <?php echo wp_json_encode( wp_create_nonce( 'ringkas_ajax' ) ); ?>,
	pollMs: 30000
};
</script>
<script src="<?php echo esc_url( RINGKAS_PLUGIN_URL . 'assets/ringkas.js' ); ?>?v=<?php echo esc_attr( RINGKAS_VERSION ); ?>" defer></script>
</body>
</html>
