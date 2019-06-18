<?php
/**
 * Model class for queue items table
 *
 * @package mpp-local-transcoder
 */

namespace MPP_Local_Transcoder\Models;

use MPP_Local_Transcoder\Schema\Schema;

/**
 * Class Queue_Item
 *
 * @property int    $id Queue id.
 * @property int    $user_id user id.
 * @property int    $media_id Media id.
 * @property string $queue_type Queue type.
 * @property string $status Current status.
 * @property int    $process_id Process id.
 * @property int    $completed Was completed?
 * @property string $created_at Date added to queue.
 * @property string $updated_at Date updated.
 */
class Queue extends Model {

	/**
	 * Get the table name.
	 *
	 * @return null|string
	 */
	public static function table() {
		return Schema::table( 'queue' );
	}

	/**
	 * Table schema.
	 *
	 * @return array
	 */
	public static function schema() {
		return array(
			'id'         => 'integer',
			'user_id'    => 'integer',
			'media_id'   => 'integer',
			'queue_type' => 'string',
			'status'     => 'string',
			'process_id' => 'integer',
			'completed'  => 'integer',
			'created_at' => 'datetime',
			'updated_at' => 'datetime',
		);
	}
}
