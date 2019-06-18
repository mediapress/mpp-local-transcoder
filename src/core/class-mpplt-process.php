<?php
/**
 * Process Management.
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
 * Process handler.
 */
class MPPLT_Process {

	/**
	 * Run task in background.
	 *
	 * @param string $command command to run.
	 *
	 * @return null|string Returns Process Id.
	 */
	public static function run( $command ) {
		return shell_exec( "nohup " . escapeshellcmd( $command ) . " 2> /dev/null & echo $!" );
	}

	/**
	 * Check if process is running.
	 *
	 * @param string $process_id process id.
	 *
	 * @return bool
	 */
	public static function is_running( $process_id ) {
		exec( escapeshellcmd( "ps $process_id" ), $process_state );

		return ( count( $process_state ) >= 2 );
	}

	/**
	 * Kill a process.
	 *
	 * @param int $process_id process id.
	 */
	public static function kill( $process_id ) {
		shell_exec( escapeshellcmd( "kill -9 $process_id 2>/dev/null" ) );
	}

	/**
	 * Check if a shell command exists.
	 *
	 * @param string $command command name.
	 *
	 * @return bool
	 */
	public static function exists( $command ) {
		return ! empty( shell_exec( sprintf( 'which %s 2>/dev/null', $command ) ) );
	}
}
