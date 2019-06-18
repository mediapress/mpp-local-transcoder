<?php
/**
 * Queue mover class
 *
 * @package    MPP Local Transcoder
 * @subpackage Core
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace MPP_Local_Transcoder\Core;

use MPP_Local_Transcoder\Schema\Schema;

// No direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MPPLT_Queue_Mover
 */
class MPPLT_Queue_Mover {

	/**
	 * Apply filters
	 */
	private function apply_filters() {
		add_filter( 'posts_where', array( $this, 'filter_by_max' ) );
		add_filter( 'posts_join', array( $this, 'apply_join' ) );
	}

	/**
	 * Remove filters
	 */
	private function remove_filters() {
		remove_filter( 'posts_where', array( $this, 'filter_by_max' ) );
		remove_filter( 'posts_join', array( $this, 'apply_join' ) );
	}

	/**
	 * Apply join
	 *
	 * @return string
	 */
	public function apply_join( $join ) {
		global $wpdb;

		$table = Schema::table( 'queue' );

		$join .= " LEFT JOIN $table ON $wpdb->posts.ID = $table.media_id ";

		return $join;
	}

	/**
	 *  Send media count.
	 */
	public function get_remaining_media_count() {
		$this->apply_filters();

		$wpq = new \WP_Query( $this->get_query_args() );

		$this->remove_filters();

		return $wpq->found_posts;
	}

	/**
	 * Get The next post ids.
	 *
	 * @return array
	 */
	public function get_next_media_ids() {

		$this->apply_filters();

		$wp = new \WP_Query( $this->get_query_args() );

		$this->remove_filters();

		return $wp->posts;
	}

	/**
	 * Where clause to filter by post id greater than last processed id
	 *
	 * @param string $where Where clause.
	 *
	 * @return string
	 */
	public function filter_by_max( $where ) {
		global $wpdb;

		$last  = get_option( '_mpplt_last_processed_id', 0 );
		$table = Schema::table( 'queue' );

		$where = $where . $wpdb->prepare( " AND {$wpdb->posts}.ID > %d AND {$table}.media_id IS NULL ", $last );

		return $where;
	}

	/**
	 * Get query args
	 *
	 * @return array
	 */
	private function get_query_args() {
		$args = array(
			'post_type'      => 'attachment',
			'posts_per_page' => 5,
			'post_status'    => 'inherit',
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => '_mpp_is_mpp_media',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => '_mpplt_encoded',
					'compare' => 'NOT EXISTS',
				),
			),
			'tax_query' => array(
				array(
					'taxonomy' => mpp_get_type_taxname(),
					'field'    => 'term_taxonomy_id',
					'terms'    => mpp_get_tt_ids( 'video', mpp_get_type_taxname() ),
					'operator' => 'IN',
				),
			),
			'fields'         => 'ids',
		);

		return $args;
	}
}