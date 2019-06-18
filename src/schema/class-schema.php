<?php
/**
 * Moderation Tools/Report Abuse Database Schema helper
 *
 * @package    MPP Local Transcoder
 * @subpackage Schema
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace MPP_Local_Transcoder\Schema;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Schema Manager.
 *
 * For actual models, Please see models directory.
 */
class Schema {

	/**
	 * Get table name.
	 *
	 * @param string $name table identifier.
	 *
	 * @return null|string full table name or null.
	 */
	public static function table( $name ) {
		global $wpdb;

		$tables = array( 'queue' => 'mpplt_trancode_queue' );

		return isset( $tables[ $name ] ) ? $wpdb->prefix . $tables[ $name ] : null;
	}

	/**
	 * Create Tables.
	 */
	public static function create() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();
		$table_queue     = self::table( 'queue' );
		$sql             = array();

		if ( ! self::exists( $table_queue ) ) {
			$sql[] = "CREATE TABLE `{$table_queue}`(
  				`id` bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  				`user_id` bigint(20) NOT NULL,
  				`media_id` bigint(20) NOT NULL,
  				`status` varchar(32) NOT NULL,
  				`process_id` bigint(20)  NOT NULL DEFAULT '0',
  				`completed` tinyint(1) NOT NULL DEFAULT '0',
  				`created_at` datetime NOT NULL,
  				`updated_at` datetime DEFAULT CURRENT_TIMESTAMP
			){$charset_collate};";
		}

		if ( ! $sql ) {
			return;
		}

		dbDelta( $sql );
	}

	/**
	 * Alter table
	 *
	 * @return bool|int|void
	 */
	public static function alter() {
		global $wpdb;

		$table_queue = self::table( 'queue' );

		if ( ! self::exists( $table_queue ) ) {
			return;
		}

		return $wpdb->query( "ALTER TABLE {$table_queue} ADD COLUMN queue_type varchar(100) NOT NULL DEFAULT 'conversion' AFTER media_id;" );
	}

	/**
	 * Check if table exists.
	 *
	 * @param string $table_name table name.
	 *
	 * @return bool
	 */
	public static function exists( $table_name ) {
		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name;
	}

	/**
	 * Drop a table from database.
	 *
	 * @param string $table_name table name.
	 *
	 * @return false|int
	 */
	public static function drop( $table_name ) {
		global $wpdb;

		return $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
	}

	/**
	 * Truncate Table.
	 *
	 * @param string $table_name table name.
	 *
	 * @return false|int
	 */
	public function truncate( $table_name ) {
		global $wpdb;

		return $wpdb->query( "TRUNCATE TABLE {$table_name}" );
	}
}
