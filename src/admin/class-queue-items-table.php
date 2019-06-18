<?php
/**
 * Queue Items List
 *
 * @package    MPP Local Transcoder
 * @subpackage Admin
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace MPP_Local_Transcoder\Admin;

use MPP_Local_Transcoder\Models\Queue;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;


/**
 * Class Queue_Items_Table
 */
class Queue_Items_Table extends \WP_List_Table {

	/**
	 * Flag vars.
	 *
	 * @var array
	 */
	private $args = array();

	/**
	 * Queue_Items_Table constructor.
	 *
	 * @param array $args Array of values.
	 */
	public function __construct( $args = array() ) {
		$this->args = $args;

		$parent_args = array(
			'singular' => 'queue_item',
			'plural'   => 'queue_items',
			'ajax'     => true,
		);

		parent::__construct( $parent_args );
	}

	/**
	 * Check user permissions
	 *
	 * @return bool
	 */
	public function ajax_user_can() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * All logic goes here
	 */
	public function prepare_items() {
		$current_page = $this->get_pagenum();
		$per_page     = 20;

		$args = array(
			'per_page'  => $per_page,
			'completed' => 0,
			'page'      => $current_page,
		);

		if ( ! empty( $this->args['hidden'] ) ) {
			$args['is_hidden'] = $this->args['hidden'];
		}

		$args['orderby'] = 'updated_at';
		$args['order']   = 'DESC';

		$this->items = Queue::get( $args );

		unset( $args['per_page'] );
		unset( $args['offset'] );

		$this->set_pagination_args(
			array(
				'total_items' => Queue::count( $args ),
				'per_page'    => $per_page,
			)
		);
	}

	/**
	 * Render message when no items found
	 */
	public function no_items() {
		esc_html_e( 'No items in queue.', 'mpp-local-transcoder' );
	}

	/**
	 * Get views
	 *
	 * @return array
	 */
	public function get_views() {
		return array();
	}

	/**
	 * Return bulk actions
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array();
	}

	/**
	 * Get column info
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'thumbnail'  => __( 'Thumbnail', 'mpp-local-transcoder' ),
			'media_id'   => __( 'Media ID', 'mpp-local-transcoder' ),
			'queue_type' => __( 'Queue type', 'mpp-local-transcoder' ),
			'status'     => __( 'Status', 'mpp-local-transcoder' ),
			'updated_at' => __( 'Added on', 'mpp-local-transcoder' ),
		);
	}

	/**
	 * Sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'media_id'   => array( 'user_id', false ),
			'updated_at' => array( 'updated_at', false ),
		);

		return $sortable_columns;
	}

	/**
	 * Column data.
	 *
	 * @param Queue  $item que item.
	 * @param string $col column name.
	 *
	 * @return string
	 */
	public function column_default( $item, $col ) {

		switch ( $col ) {
			case 'id':
				return $item->id;
				break;

			case 'media_id':
				return $item->media_id;
				break;

			case 'queue_type':
				return $item->queue_type;
				break;

			case 'status':
				$class = 'queued' == $item->status ? 'mpplt-queued' : 'mpplt-processing';

				return sprintf( '<strong class="%s">%s</strong>', $class, $item->status );
				break;

			case 'updated_at':
				return mysql2date( 'g:i:s A, F j, Y', $item->updated_at );
				break;
		}
	}

	/**
	 * Get the item column.
	 *
	 * @param Queue $item queue item.
	 *
	 * @return string
	 */
	public function column_thumbnail( $item ) {
		$media = mpp_get_media( $item->media_id );

		return mpp_get_media_title( $media );
	}

	/**
	 * Handle row actions.
	 *
	 * @param Queue  $item Queue item object.
	 * @param string $column_name column name.
	 * @param string $primary is primary column.
	 *
	 * @return bool|int|string
	 */
	public function handle_row_actions( $item, $column_name, $primary ) {
		$actions = array();

		switch ( $column_name ) {
			case 'item':
			case 'actions':
			case 'thumbnail':
				$view_url = mpp_get_media_permalink( $item->media_id );

				$actions = array(
					'view' => sprintf( '<a href="%s" title="%s">%s</a>', $view_url, __( 'View', 'mpp-local-transcoder' ), __( 'View', 'mpp-local-transcoder' ) ),
				);

				break;
		}

		return $this->row_actions( $actions );
	}

	/**
	 * Filter list by moderation state
	 *
	 * @param string $which Whether this is being invoked above ("top")
	 *                      or below the table ("bottom").
	 */
	public function extra_tablenav( $which ) {
		if ( 'bottom' === $which ) {
			return;
		}
	}
}
