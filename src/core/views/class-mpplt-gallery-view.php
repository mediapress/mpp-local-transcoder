<?php
/**
 * Gallery view
 *
 * @package    MPP Local Transcoder
 * @subpackage Core\View
 */

namespace MPP_Local_Transcoder\Core\Views;

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A view that filters current gallery view and either shows media not available or delegates the check back to existing veiw.
 */
class MPPLT_Gallery_View extends \MPP_Gallery_View {
	/**
	 * Reference to the old view.
	 *
	 * @var \MPP_Gallery_View;
	 */
	private $old_view;

	/**
	 * Constructor.
	 *
	 * @param \MPP_Gallery_View $view old view.
	 */
	public function __construct( \MPP_Gallery_View $view ) {
		parent::__construct();

		$this->old_view = $view;

		$this->id   = 'mpplt-under-conversion';
		$this->name = __( 'Under conversion', 'mpp-local-transcoder' );
	}

	/**
	 * Display single gallery.
	 * We will delegate to the existing gallery view as the mediapress()->the_gallery_query/media_query is filtered.
	 *
	 * @param \MPP_Gallery $gallery gallery object.
	 */
	public function display( $gallery ) {
		$this->old_view->display( $gallery );
	}

	/**
	 * Display for activity
	 *
	 * @param int[] $media_ids media ids.
	 * @param int   $activity_id Activity id.
	 *
	 * @return null
	 */
	public function activity_display( $media_ids = array(), $activity_id = 0 ) {

		if ( ! $media_ids ) {
			return;
		}

		$activity_id = empty( $activity_id ) ? bp_get_activity_id() : $activity_id;

		// Filter the media which are in moderation.
		$filtered_ids = $this->get_filtered_ids( $media_ids );

		// If there are still some media that can be seen by the current user, let us delegate the view.
		if ( ! empty( $filtered_ids ) ) {
			$this->old_view->activity_display( $filtered_ids, $activity_id );

			return;
		}

		// if we are here, all attached media are not viewable by the user.
		// In this case, we show a media not available message.
		$templates = array( 'transcoder/media-under-conversion.php' );

		$located_template = mpp_locate_template( $templates, false, mpp_local_storage()->get_path() . 'templates' );

		if ( $located_template ) {
			include $located_template;
		}
	}

	/**
	 * Get filtered media ids(array after removing hidden media).
	 *
	 * @param array $media_ids media ids.
	 *
	 * @return array
	 */
	private function get_filtered_ids( $media_ids ) {
		// if we are here, we need to filter the media list right?
		$filtered_ids = array();
		foreach ( $media_ids as $media_id ) {

			if ( mpp_get_media_meta( $media_id, '_mpplt_converting_file_original', true ) ) {
				continue;
			}

			$filtered_ids[] = $media_id;
		}

		return $filtered_ids;
	}
}
