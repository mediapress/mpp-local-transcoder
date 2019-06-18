<?php
/**
 * Parser for FFMPEG Generated Progress file.
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
 * FFMPEG Generated Progress file parser.
 */
class MPPLT_Status_Reader {

	/**
	 * Progress file path.
	 *
	 * @var string
	 */
	private $file_path;

	/**
	 * MPPLT_Status_Reader constructor.
	 *
	 * @param string $file_path status/progress file path.
	 */
	public function __construct( $file_path ) {
		$this->file_path = $file_path;
	}

	/**
	 * Get Conversion Status details.
	 *
	 * @return array|null
	 */
	public function get_status() {

		if ( ! is_readable( $this->file_path ) ) {
			return null;
		}

		$contents = file( $this->file_path, FILE_SKIP_EMPTY_LINES );

		if ( empty( $contents ) ) {
			return null;
		}

		return $this->to_array( $contents );
	}

	/**
	 * Get the path for the temporary media progress file.
	 *
	 * @param int    $media_id media id.
	 * @param string $prefix media id.
	 *
	 * @return bool|string
	 */
	public static function get_progress_file_path( $media_id, $prefix = 'mpplt-' ) {
		return trailingslashit( dirname( get_attached_file( $media_id ) ) ) . $prefix . $media_id . '.progress';
	}

	/**
	 * Convert lines to associative array.
	 *
	 * @param array $lines lines array.
	 *
	 * @return array
	 */
	private function to_array( $lines ) {
		$parsed = array();

		/*
		$line_text = join('&', $lines );
		parse_str( $line_text, $parsed );
		*/

		foreach ( $lines as $line ) {
			$tokens                       = explode( '=', $line );
			$parsed[ trim( $tokens[0] ) ] = isset( $tokens[1] ) ? trim( $tokens[1] ) : '';
		}

		return $parsed;
	}
}
