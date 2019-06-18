<?php
/**
 * Admin functions.
 *
 * @package    MPP Local Transcoder.
 * @subpackage Admin
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Show admin notice if any.
 */
function mpplt_admin_notice() {
    // phpcs:disable
	if ( empty( $_GET['message'] ) ) {
		return;
	}

	$type = isset( $_GET['message-type'] ) ? trim( $_GET['message-type'] ) : 'fade';
	?>
    <div class="updated <?php echo esc_attr( $type ); ?>">
        <p><?php echo wp_kses_data( $_GET['message'] ); ?></p>
    </div>
	<?php
	// phpcs:enable
}

/**
 * Get url for the queue item pages.
 *
 * @param array $args args.
 *
 * @return string
 */
function mpplt_admin_get_queue_url( $args = array() ) {
	$args = wp_parse_args( $args, array( 'page' => 'mpplt-queue' ) );

	if ( empty( $args['mpplt_state'] ) ) {
		unset( $args['mpplt_state'] );
	}

	$args = urlencode_deep( $args );

	return add_query_arg( $args, admin_url( 'admin.php' ) );
}

/**
 * Get url for the log item pages.
 *
 * @param array $args args.
 *
 * @return string
 */
function mpplt_admin_get_log_url( $args = array() ) {
	$args = wp_parse_args( $args, array( 'page' => 'mpplt-log' ) );

	return add_query_arg( $args, admin_url( 'admin.php' ) );
}
