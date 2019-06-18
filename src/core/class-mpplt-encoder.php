<?php
/**
 * MediaPress Local Transcoder
 *
 * @package    MPP Local Transcoder
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace MPP_Local_Transcoder\Core;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * MPPLT_Encoder class.
 */
class MPPLT_Encoder {

	/**
	 * Encode a media
	 *
	 * @param int $media_id media id.
	 *
	 * @return bool|string|null
	 */
	public function encode( $media_id ) {
		$media = mpp_get_media( $media_id );

		if ( 'video' !== $media->type || $media->is_remote ) {
			return false;
		}

		$log_file = MPPLT_Status_Reader::get_progress_file_path( $media_id );

		// if we are here, it is mediapress video file and local.
		// Let us get the main file.
		$file = get_attached_file( $media_id );

		if ( ! $file ) {
			return false;
		}

		$info      = pathinfo( $file );
		$extension = $info['extension'];

		if ( empty( $extension ) ) {
			return false;
		}

		$converted_file_name = $info['dirname'] . '/' . wp_unique_filename( $info['dirname'], $info['filename'] . '.mp4' );

		mpp_update_media_meta( $media_id, '_mpplt_converting_file_original', $converted_file_name );

		$command = "ffmpeg -i {$file} -movflags faststart -progress {$log_file} ";

		$options = $this->prepare_video_options( $extension, 'original' );

		$command = $command . $options . ' ' . $converted_file_name;

		return MPPLT_Process::run( $command );
	}

	/**
	 * Prepare options.
	 *
	 * @param string $extension extension.
	 * @param string $dim_name dimension name.
	 *
	 * @return string
	 */
	public function prepare_video_options( $extension, $dim_name ) {
		$options = array();

		if ( 'original' == $dim_name ) {
			$options[] = $this->get_format_options( $extension );
		} else {
			$options[] = $this->get_dimension_options( $dim_name );
			$options[] = '-strict -2';// enable experimental aac.
		}

		return join( ' ', $options );
	}

	/**
	 * Get format options for an extension.
	 *
	 * @param string $extension extension.
	 *
	 * @return string
	 */
	private function get_format_options( $extension ) {
		$options = array(
			'mov'  => '-codec copy',
			'mpeg' => '-c:v libx264',
			'mkv'  => '-vcodec h264 -acodec aac -strict -2', //'-c copy',.
			'mp4'  => '-vcodec h264 -acodec aac -strict -2', // -acodec mp2.
			'flv'  => '-strict -2',
			'avi'  => '-strict -2',
		);

		return isset( $options[ $extension ] ) ? $options[ $extension ] : '';
	}

	/**
	 * Get dimension option.
	 *
	 * @param string $dim_name resolution name.
	 *
	 * @return string
	 */
	private function get_dimension_options( $dim_name ) {
		$dimensions = array(
			'res_720' => array(
				'height' => 720,
				'width'  => 1280,
			),
			'res_480' => array(
				'height' => 480,
				'width'  => 640,
			),
			'res_240' => array(
				'height' => 240,
				'width'  => 320,
			),
		);

		$dim = isset( $dimensions[ $dim_name ] ) ? $dimensions[ $dim_name ] : false;

		if ( ! $dim ) {
			return '';
		}

		return $this->get_scale_option( $dim['width'], $dim['height'] );
	}

	/**
	 * Get scale option.
	 *
	 * @param int $width width.
	 * @param int $height height.
	 *
	 * @return string
	 */
	private function get_scale_option( $width, $height ) {
		return "-s {$width}x{$height}";
	}

	/**
	 * Extract image.
	 *
	 * @param int $media_id media id.
	 *
	 * @return bool|string|null
	 */
	public function extract_image( $media_id ) {
		$media = mpp_get_media( $media_id );

		if ( 'video' !== $media->type || $media->is_remote ) {
			return false;
		}

		if ( mpp_get_media_meta( $media_id, '_mpplt_preview_generated', true ) ) {
			return false;
		}

		$log_file = MPPLT_Status_Reader::get_progress_file_path( $media_id, 'mpplt-preview-' );

		// if we are here, it is mediapress video file and local.
		// Let us get the main file.
		$file = get_attached_file( $media_id );

		if ( ! $file ) {
			return false;
		}

		$info      = pathinfo( $file );
		$extension = $info['extension'];

		if ( empty( $extension ) ) {
			return false;
		}

		$covers_dir = wp_mkdir_p( trailingslashit( $info['dirname'] ) . 'covers' );

		if ( ! $covers_dir ) {
			return false;
		}

		$covers_dir_path = trailingslashit( $info['dirname'] ) . 'covers/';

		$cover_file      = wp_unique_filename( $covers_dir_path, $media_id . '-preview.jpg' );
		$cover_file_path = $covers_dir_path . $cover_file;

		mpp_update_media_meta( $media_id, '_mpplt_preview_image_file', $cover_file_path );

		$command = "ffmpeg -i {$file} -ss 1.5 -progress {$log_file} -vframes 1 $cover_file_path";

		return MPPLT_Process::run( $command );
	}
}
