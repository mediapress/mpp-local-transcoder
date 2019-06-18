<?php
/**
 * MediaPress Local Transcoder Task Runner.
 *
 * @package    MPP Local Transcoder
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace MPP_Local_Transcoder\Cron;

use MPP_Local_Transcoder\Core\MPPLT_Encoder;
use MPP_Local_Transcoder\Core\MPPLT_Process;
use MPP_Local_Transcoder\Core\MPPLT_Status_Reader;
use MPP_Local_Transcoder\Models\Queue;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Task Runner.
 */
class MPPLT_Task_Runner {

	/**
	 * Boot this class.
	 */
	public static function boot() {
		$self = new self();
		$self->setup();

		return $self;
	}

	/**
	 * Setup hooks callbacks
	 */
	public function setup() {
		// Process Queue on cron.
		add_action( 'mpplt_process_queue', array( $this, 'process' ) );
		// Generate preview.
		add_action( 'mpplt_process_queue_preview_generation', array( $this, 'process_preview_generation' ) );
	}

	/**
	 * Process queue.
	 */
	public function process() {
		// FFMPEG must exist.
		if ( ! MPPLT_Process::exists( 'ffmpeg' ) ) {
			return;
		}

		$this->check_update_processing_status();
		$this->process_conversions();
	}

	/**
	 * Process preview generation
	 */
	public function process_preview_generation() {
		// FFMPEG must exist.
		if ( ! MPPLT_Process::exists( 'ffmpeg' ) ) {
			return;
		}

		$this->check_update_preview_processing_status();
		$this->process_preview_generations();
	}

	/**
	 * Check and update the processing queue entries for task status..
	 */
	public function check_update_processing_status() {
		// get next 5 new videos.
		$items = mpplt_get_items(
			array(
				'completed'  => 0,
				'status'     => 'processing',
				'queue_type' => 'conversion',
				'orderby'    => 'created_at',
				'order'      => 'ASC',
				'per_page'   => 5,
			)
		);

		if ( empty( $items ) ) {
			return;
		}

		foreach ( $items as $item ) {
			$this->update_state( $item );
		}
	}

	/**
	 * Check and update the processing queue entries for task status..
	 */
	public function check_update_preview_processing_status() {
		// get next 5 new videos.
		$items = mpplt_get_items(
			array(
				'completed'  => 0,
				'status'     => 'processing',
				'queue_type' => 'preview_generation',
				'orderby'    => 'created_at',
				'order'      => 'ASC',
				'per_page'   => 5,
			)
		);

		if ( empty( $items ) ) {
			return;
		}

		foreach ( $items as $item ) {
			$this->update_preview_state( $item );
		}
	}

	/**
	 * Update state.
	 *
	 * @param Queue $item queue entry.
	 */
	private function update_state( $item ) {
		$log_file = MPPLT_Status_Reader::get_progress_file_path( $item->media_id );

		$status_reader = new MPPLT_Status_Reader( $log_file );
		$status        = $status_reader->get_status();

		if ( empty( $status ) ) {
			$this->cleanup_on_error( $item->media_id );
			// Clear flag and save.
			$item->status    = 'error';
			$item->completed = 1;

			mpplt_update_item( $item );

			@unlink( $log_file );

			return; // Not found.
		}

		// not completed yet.
		// Should we check for process end and status error.
		if ( ! isset( $status['progress'] ) && 'end' !== $status['progress'] ) {
			return;
		}

		$file_name = mpp_get_media_meta( $item->media_id, '_mpplt_converting_file_original', true );
		$old_file  = get_attached_file( $item->media_id );

		update_attached_file( $item->media_id, $file_name );
		// @todo Why not delete origin file
		update_post_meta( $item->media_id, '_mpplt_raw_file_original', _wp_relative_upload_path( $old_file ) );

		// it is completed.
		$item->completed  = 1;
		$item->status     = 'completed';
		$item->process_id = 0;

		mpplt_update_item( $item );

		$stat  = @stat( dirname( $file_name ) );
		$perms = $stat['mode'] & 0007777;
		$perms = $perms & 0000666;

		@chmod( $file_name, $perms );
		clearstatcache();

		// Save that we encoded it.
		update_post_meta( $item->media_id, '_mpplt_encoded', 1 );
		delete_post_meta( $item->media_id, '_mpplt_converting_file_original' );

		@unlink( $log_file );
	}

	/**
	 * Update preview state
	 *
	 * @param Queue $item Queue item.
	 */
	private function update_preview_state( $item ) {
		$log_file = MPPLT_Status_Reader::get_progress_file_path( $item->media_id, 'mpplt-preview-' );

		$status_reader = new MPPLT_Status_Reader( $log_file );
		$status        = $status_reader->get_status();

		if ( empty( $status ) ) {
			$file = mpp_get_media_meta( $item->media_id, '_mpplt_preview_image_file', true );
			mpp_delete_media_meta( $item->media_id, '_mpplt_preview_image_file' );

			@unlink( $file );

			// Clear flag and save.
			$item->status    = 'error';
			$item->completed = 1;

			mpplt_update_item( $item );

			@unlink( $log_file );

			return; // Not found.
		}

		// not completed yet.
		if ( ! isset( $status['progress'] ) && 'end' !== $status['progress'] ) {
			return;
		}

		$preview_image_path = mpp_get_media_meta( $item->media_id, '_mpplt_preview_image_file', true );

		if ( ! is_readable( $preview_image_path ) ) {
			return;
		}

		// include from wp-admin dir for media processing.
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$attachment = array(
			'post_mime_type' => mime_content_type( $preview_image_path ),
			'post_type'      => 'attachment',
			'post_content'   => '',
		);

		$sub_attachment_id = wp_insert_attachment( $attachment, $preview_image_path );
		$attach_data       = mpp_generate_media_metadata( $sub_attachment_id, $preview_image_path );

		wp_update_attachment_metadata( $sub_attachment_id, $attach_data );

		// if the option is set to set post thumbnail.
		if ( mpp_get_option( 'set_post_thumbnail' ) ) {
			mpp_update_media_meta( $item->media_id, '_thumbnail_id', $sub_attachment_id );
		}

		// set the cover id.
		mpp_update_media_cover_id( $item->media_id, $sub_attachment_id );

		// it is completed.
		$item->completed  = 1;
		$item->status     = 'completed';
		$item->process_id = 0;

		mpplt_update_item( $item );

		$stat  = @stat( dirname( $preview_image_path ) );
		$perms = $stat['mode'] & 0007777;
		$perms = $perms & 0000666;

		@chmod( $preview_image_path, $perms );
		clearstatcache();

		// Save that we encoded it.
		update_post_meta( $item->media_id, '_mpplt_preview_generated', 1 );
		delete_post_meta( $item->media_id, '_mpplt_preview_image_file' );

		@unlink( $log_file );
	}

	/**
	 * Cleanup error
	 *
	 * @param int $media_id Media id.
	 */
	private function cleanup_on_error( $media_id ) {
		$file = mpp_get_media_meta( $media_id, '_mpplt_converting_file_original', true );
		mpp_delete_media_meta( $media_id, '_mpplt_converting_file_original' );

		@unlink( $file );
	}

	/**
	 * Process conversion of media from queue.
	 */
	public function process_conversions() {
		// Get next 5 new videos.
		$items = mpplt_get_items(
			array(
				'completed'  => 0,
				'status'     => 'queued',
				'queue_type' => 'conversion',
				'orderby'    => 'created_at',
				'order'      => 'ASC',
				'per_page'   => 5,
			)
		);

		if ( empty( $items ) ) {
			return;
		}

		foreach ( $items as $item ) {
			$this->convert( $item );
		}
	}

	/**
	 * Process conversion of media from queue.
	 */
	public function process_preview_generations() {
		// Get next 5 new videos.
		$items = mpplt_get_items(
			array(
				'completed'  => 0,
				'status'     => 'queued',
				'queue_type' => 'preview_generation',
				'orderby'    => 'created_at',
				'order'      => 'ASC',
				'per_page'   => 5,
			)
		);

		if ( empty( $items ) ) {
			return;
		}

		foreach ( $items as $item ) {
			$this->generate_preview( $item );
		}
	}

	/**
	 * Convert an entry.
	 *
	 * @param Queue $item queue entry.
	 */
	private function convert( $item ) {
		$media_id = $item->media_id;
		$media    = mpp_get_media( $media_id );

		// Delete invalid entry.
		if ( 'video' !== $media->type || $media->is_remote ) {
			$item->delete();

			return;
		}

		// Now, we know that it is the video.
		$encoder = new MPPLT_Encoder();
		$job_id  = $encoder->encode( $media_id );

		if ( ! $job_id ) {
			return;
		}

		$item->process_id = $job_id;
		$item->status     = 'processing';

		mpplt_update_item( $item );
	}

	/**
	 * Convert an entry.
	 *
	 * @param Queue $item queue entry.
	 */
	private function generate_preview( $item ) {
		$media_id = $item->media_id;
		$media    = mpp_get_media( $media_id );

		// Delete invalid entry.
		if ( 'video' !== $media->type || $media->is_remote ) {
			$item->delete();

			return;
		}

		// Now, we know that it is the video.
		$encoder = new MPPLT_Encoder();
		$job_id  = $encoder->extract_image( $media_id );

		if ( ! $job_id ) {
			return;
		}

		$item->process_id = $job_id;
		$item->status     = 'processing';

		mpplt_update_item( $item );
	}
}
