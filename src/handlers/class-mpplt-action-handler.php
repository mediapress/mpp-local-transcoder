<?php
/**
 * MediaPress Local Transcoder Action Handler.
 *
 * @package    MPP Local Transcoder
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace MPP_Local_Transcoder\Handlers;

use MPP_Gallery;
use MPP_Gallery_View;
use MPP_Local_Transcoder\Core\MPPLT_Process;
use MPP_Local_Transcoder\Core\Views\MPPLT_Gallery_View;
use MPP_Local_Transcoder\Core\Views\MPPLT_Media_View;
use MPP_Local_Transcoder\Models\Queue;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Action handler.
 */
class MPPLT_Action_Handler {

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
		// on media add, add to queue
		// on media delete, delete from queue.
		add_action( 'mpp_media_added', array( $this, 'on_media_add' ) );
		add_action( 'delete_attachment', array( $this, 'on_media_delete' ) );

		add_action( 'mpplt_queue_item_added', array( $this, 'queue_item_added' ) );
		add_action( 'mpplt_queue_item_updated', array( $this, 'queue_item_updated' ) );

		add_filter( 'mpp_get_media_view', array( $this, 'filter_media_view' ), 100, 2 );
		add_filter( 'mpp_get_gallery_view', array( $this, 'filter_gallery_view' ), 100, 2 );
		add_filter( 'mpp_get_activity_view', array( $this, 'filter_activity_view' ), 100, 2 );
	}

	/**
	 * Add to Queue on media add.
	 *
	 * @param int $media_id media id.
	 */
	public function on_media_add( $media_id ) {
		$media = mpp_get_media( $media_id );

		if ( is_null( $media ) || 'video' !== $media->type || $media->is_remote ) {
			return;
		}

		// Add to conversion queue.
		mpplt_add_item( array( 'media_id' => $media_id ) );

		// Add to video preview generation queue.
		mpplt_add_item(
			array(
				'media_id'   => $media_id,
				'queue_type' => 'preview_generation',
			)
		);
	}

	/**
	 * On Media delete.
	 *
	 * @param int $media_id media id.
	 */
	public function on_media_delete( $media_id ) {

		if ( ! function_exists( 'mediapress' ) || ! mpp_is_valid_media( $media_id ) ) {
			return;
		}

		$media = mpp_get_media( $media_id );

		if ( is_null( $media ) || 'video' !== $media->type || $media->is_remote ) {
			return;
		}

		$items = Queue::get( array( 'media_id' => $media_id ) );

		if ( empty( $items ) ) {
			return;
		}

		foreach ( $items as $item ) {

			if ( 'processing' == $item->status && $item->process_id ) {
				// Kill the process.
				MPPLT_Process::kill( $item->process_id );
			}

			$item->delete();// remove.

			$original = update_post_meta( $item->media_id, '_mpplt_raw_file_original', true );

			if ( ! $original ) {
				return;
			}

			// delete the half processed file.
			$info = wp_upload_dir();
			$file = path_join( $info['basedir'], $original );

			// delete entry.
			@unlink( $file );
		}
	}

	/**
	 * On item added to queue.
	 *
	 * @param Queue $item Queue item.
	 */
	public function queue_item_added( $item ) {
		// Only handled for conversion case.
		if ( 'conversion' == $item->queue_type && 'queued' == $item->status ) {
			mpp_update_media_meta( $item->media_id, '_mpplt_media_queued', 1 );
		}
	}

	/**
	 * On item added to queue.
	 *
	 * @param Queue $item Queue item.
	 */
	public function queue_item_updated( $item ) {
		// Only handled for conversion case. if process ended with error continue to show default image.
		if ( 'conversion' == $item->queue_type && 'completed' == $item->status ) {
			mpp_delete_media_meta( $item->media_id, '_mpplt_media_queued' );
		}
	}

	/**
	 * Check if media needs filtered view or not
	 *
	 * @param int $media_id Media id.
	 *
	 * @return bool
	 */
	private function needs_filtered_view( $media_id ) {

		if ( mpp_get_media_meta( $media_id, '_mpplt_media_queued', true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get media src
	 *
	 * @param \MPP_Media_View $view  View object.
	 * @param \MPP_Media      $media media object.
	 *
	 * @return string
	 */
	public function filter_media_view( $view, $media ) {
		// Make sure file is in encoding process.
		if ( $this->needs_filtered_view( $media->id ) ) {
			$view = new MPPLT_Media_View();
		}

		return $view;
	}

	/**
	 * Filter media view to generate single media view.
	 *
	 * @param MPP_Gallery_View $view Gallery View object.
	 * @param MPP_Gallery      $gallery gallery object.
	 *
	 * @return MPP_Gallery_View
	 */
	public function filter_gallery_view( $view, $gallery ) {
		$media_ids = mpp_get_all_media_ids( array( 'gallery_id' => $gallery->id ) );

		if ( empty( $media_ids ) ) {
			return $view;
		}

		foreach ( $media_ids as $media_id ) {
			if ( $this->needs_filtered_view( $media_id ) ) {
				$view = new MPPLT_Media_View();
			}
		}

		return $view;
	}

	/**
	 * Filter media view to generate single media view.
	 *
	 * @param MPP_Gallery_View $view Gallery View object.
	 * @param string           $type media type.
	 *
	 * @return MPP_Gallery_View
	 */
	public function filter_activity_view( $view, $type ) {
		$view = new MPPLT_Gallery_View( $view );

		return $view;
	}
}
