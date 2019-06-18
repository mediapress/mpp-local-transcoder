<?php
/**
 * Functions
 *
 * @package    MPP Local Transcoder
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

use MPP_Local_Transcoder\Models\Queue;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Add item to queue.
 *
 * @param array $args Array {.
 *  @type string $status     Queue status always queued.
 *  @type int    $user_id    User id who has uploaded the video.
 *  @type int    $media_id   Media id added to queue.
 *  @type string $queue_type Queue type either conversion or preview_generation.
 *  @type int    $process_id Queue running process id default 0.
 *  @type int    $completed  Weather completed or not.
 * }
 *
 * @return bool|Queue
 */
function mpplt_add_item( $args ) {
	$default = array(
		'status'     => 'queued',
		'user_id'    => get_current_user_id(),
		'media_id'   => 0,
		'queue_type' => 'conversion',
		'process_id' => 0,
		'completed'  => 0,
	);

	$args = wp_parse_args( $args, $default );

	$item = Queue::create( $args );

	if ( $item->save() ) {
		do_action( 'mpplt_queue_item_added', $item );

		return true;
	}

	return false;
}

/**
 * Update an item.
 *
 * @param Queue $item Object {.
 *  @type int    $id         Queue id.
 *  @type string $status     Queue status always queued.
 *  @type int    $user_id    User id who has uploaded the video.
 *  @type int    $media_id   Media id added to queue.
 *  @type string $queue_type Queue type either conversion or preview_generation.
 *  @type int    $process_id Queue running process id default 0.
 *  @type int    $completed  Weather completed or not.
 * }
 *
 * @return bool|Queue
 */
function mpplt_update_item( $item ) {

	if ( ! $item->id ) {
		return false;
	}

	if ( $item->save() ) {
		do_action( 'mpplt_queue_item_updated', $item );

		return true;
	}

	return false;
}

/**
 * Get queue items
 *
 * @param array $args Array {.
 *  @type string $status     Queue status always queued.
 *  @type int    $user_id    User id who has uploaded the video.
 *  @type int    $media_id   Media id added to queue.
 *  @type string $queue_type Queue type either conversion or preview_generation.
 *  @type int    $process_id Queue running process id default 0.
 *  @type int    $completed  Weather completed or not.
 * }
 *
 * @return Queue[]
 */
function mpplt_get_items( $args = array() ) {
	$default = array(
		'completed'  => 0,
		'status'     => 'processing',
		'queue_type' => 'conversion',
		'orderby'    => 'created_at',
		'order'      => 'ASC',
		'per_page'   => 5,
	);

	$args = wp_parse_args( $args, $default );

	return Queue::get( $args );
}

/**
 * Do a safe redirect.
 *
 * @param string $url where to redirect.
 */
function mpplt_redirect( $url ) {
	wp_safe_redirect( $url );
	exit( 0 );
}
