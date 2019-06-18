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
		// if we are here, all attached media are not viewable by the user.
		// In this case, we show a media not available message.
		$templates = array( 'transcoder/media-under-conversion.php' );

		$located_template = mpp_locate_template( $templates, false, mpplt_local_transcoder()->path . 'templates/' );

		if ( $located_template ) {
			include $located_template;
		}
	}
}
