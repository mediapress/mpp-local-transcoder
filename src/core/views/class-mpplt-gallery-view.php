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
		//$this->old_view->display( $gallery );

		$templates = array(
			"gallery/views/grid-video.php", // grid-audio.php etc .
		);

		mpp_locate_template( $templates, true, mpplt_local_transcoder()->path . 'templates/' );
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

		$media = $media_ids[0];

		$media = mpp_get_media( $media );

		if ( ! $media ) {
			return;
		}

		$type = $media->type;
		// we will use include to load found template file,
		// the file will have $media_ids available.
		$templates = array(
			"buddypress/activity/views/grid-{$type}.php", // loop-audio.php etc.
		);

		$located_template = mpp_locate_template( $templates, false, mpplt_local_transcoder()->path . 'templates/' );

		include $located_template;
	}
}
