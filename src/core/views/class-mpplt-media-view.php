<?php
/**
 * Media view for default thumbnail
 *
 * @package MPP Local Transcoder
 * @subpackage Core\View
 */

namespace MPP_Local_Transcoder\Core\Views;

// Exit if the file is accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * We filter the media view and override to not available.
 */
class MPPLT_Media_View extends \MPP_Media_View {

	/**
	 * Display media.
	 *
	 * @param \MPP_Media $media media object.
	 */
	public function display( $media ) {
		mpp_get_template( 'gallery/media/views/photo.php', array(), mpplt_local_transcoder()->path . 'templates/' );
	}
}
