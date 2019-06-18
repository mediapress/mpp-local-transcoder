<?php
/**
 * MediaPress Local Transcoder Ajax Action Handler.
 *
 * @package    MPP Local Transcoder
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace MPP_Local_Transcoder\Handlers;

use MPP_Local_Transcoder\Core\MPPLT_Queue_Mover;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * MPPLT_Ajax_Handler class.
 */
class MPPLT_Ajax_Handler {

	/**
	 * Boot this class.
	 */
	public static function boot() {
		$self = new self();
		$self->setup();
	}

	/**
	 * Setup hooks callbacks
	 */
	public function setup() {
		add_action( 'wp_ajax_mpplt_get_progress', array( $this, 'progress' ) );
		// Add bulk videos to queue.
		add_action( 'wp_ajax_mpplt_bulk_process', array( $this, 'bulk_process' ) );
	}

	/**
	 * Send Progress details.
	 */
	public function progress() {
		$media_id = isset( $_POST['media_id'] ) ? absint( $_POST['media_id'] ) : 0;

		if ( empty( $media_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid action.', 'mpp-local-transcoder' ) ) );
		}

		$media = mpp_get_media( $media_id );

		if ( ! $media || ! mpp_user_can_view_media( $media ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid media.', 'mpp-local-transcoder' ) ) );
		}

		if ( 'video' !== $media->type || $media->is_remote ) {
			return;
		}

		wp_send_json_error( array( 'message' => __( 'Invalid media.', 'mpp-local-transcoder' ) ) );
	}

	/**
	 * Process bulk videos by adding theme to queue
	 */
	public function bulk_process() {
		check_ajax_referer( 'mpplt-bulk-process' );

		// check privileges.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_success(
				array(
					'remaining_items' => 0,
					'message'         => __( 'Invalid access.', 'mpp-local-transcoder' ),
				)
			);
		}

		$remaining_count = $this->get_remaining_media_count();
		$next_media_ids  = $this->get_next_media_ids();

		if ( empty( $next_media_ids ) ) {
			wp_send_json_success(
				array(
					'remaining_items' => 0,
					'message'         => __( 'No video found.', 'mpp-local-transcoder' ),
				)
			);
		}

		if ( empty( $remaining_count ) || $remaining_count < 0 ) {
			wp_send_json_success(
				array(
					'remaining_items' => 0,
					'message'         => __( 'All Done.', 'mpp-local-transcoder' ),
				)
			);
		}

		foreach ( $next_media_ids as $media_id ) {
			$add = mpplt_add_item( array( 'media_id' => $media_id ) );

			$add_to_preview = mpplt_add_item(
				array(
					'media_id'   => $media_id,
					'queue_type' => 'preview_generation',
				)
			);

			if ( ! $add || ! $add_to_preview ) {
				wp_send_json_error(
					array(
						'remaining_items' => $remaining_count,
						'message'         => __( 'There was a problem.', 'mpp-local-transcoder' ),
					)
				);
			} else {
				$remaining_count--;

				update_option( '_mpplt_last_processed_id', $media_id );
			}
		}

		delete_option( '_mpplt_last_processed_id' );

		wp_send_json_success(
			array(
				'remaining_items' => $remaining_count,
				'message'         => __( 'All Done.', 'mpp-local-transcoder' ),
			)
		);
	}

	/**
	 * Get the remaining media count.
	 *
	 * @return int
	 */
	private function get_remaining_media_count() {
		$mover = new MPPLT_Queue_Mover();
		return $mover->get_remaining_media_count();
	}

	/**
	 * Get The next post ids.
	 *
	 * @return array
	 */
	private function get_next_media_ids() {
		$mover = new MPPLT_Queue_Mover();
		return $mover->get_next_media_ids();
	}
}
