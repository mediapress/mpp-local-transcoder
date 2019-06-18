<?php
/**
 * Single Queue entry.
 *
 * @package    MPP Local Transcoder
 * @subpackage Admin/Queue
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

?>

<div class="wrap">
	<h1 class="wp-heading-inline">
        <span class="mpplt-title-type"><?php esc_html_e( 'Process old videos', 'mpp-local-transcoder' ); ?></span>
	</h1>
    <p><?php esc_html_e( 'It will add previously uploaded video to the queue for conversion and adding preview thumbnail', 'mpp-local-transcoder' ); ?></p>
	<hr class="wp-header-end">
		<p id="mpplt-notifier"></p>
	<hr />

	<form method="post" id="mpplt-bulk-process-form" action="">
		<?php wp_nonce_field( 'mpplt-bulk-process' ); ?>
		<input type="hidden" name="action" value="mpplt_bulk_process">
		<input type="submit" id="mpplt-bulk-process-btn" class="button button-primary" value="<?php _e( 'Add old videos', 'mpp-local-transcoder' ); ?>">
	</form>
</div>
