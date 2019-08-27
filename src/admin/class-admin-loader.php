<?php
/**
 * Admin Loader.
 *
 * @package    MPP Local Transcoder
 * @subpackage Admin
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace MPP_Local_Transcoder\Admin;

use MPP_Local_Transcoder\Core\MPPLT_Process;
use MPP_Local_Transcoder\Models\Queue;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Class Admin_Loader
 */
class Admin_Loader {

	/**
	 * Table object.
	 *
	 * @var string('queue' |'log')
	 */
	private $table_type = null;

	/**
	 * Queue entry id.
	 *
	 * @var int
	 */
	private $queue_id = 0;

	/**
	 * Queue entry id.
	 *
	 * @var int
	 */
	private $log_id = 0;

	/**
	 * Current action.
	 *
	 * @var string
	 */
	private $action = '';

	/**
	 * Current state filter(all| hidden).
	 *
	 * @var int
	 */
	private $hidden = 0;

	/**
	 * Message/Notice.
	 *
	 * @var string
	 */
	private $message = '';

	/**
	 * Page slug.
	 *
	 * @var string
	 */
	private $page_slug = '';

	/**
	 * The constructor.
	 */
	public function __construct() {
	}

	/**
	 * Boot this loader.
	 */
	public static function boot() {
		$self = new self();
		$self->setup();
	}

	/**
	 * Setup callbacks on necessary admin hooks
	 */
	public function setup() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'init' ) );
		add_filter( 'plugin_action_links_' . mpplt_local_transcoder()->basename, array( $this, 'plugin_action_links' ) );
		add_action( 'admin_notices', array( $this, 'ffmpeg_notice' ) );
	}

	/**
	 * Throw plugin notices
	 */
	public function ffmpeg_notice() {

		if ( MPPLT_Process::exists( 'ffmpeg' ) ) {
			return;
		}

		// phpcs:disable
		?>
        <div class="notice notice-warning is-dismissible">
            <p><?php esc_html_e( "Transcoder needs FFMPEG. You don't seem to have it installed.", 'mpp-local-transcoder' ); ?></p>
        </div>
		<?php
		// phpcs:enable
	}

	/**
	 * Register new menu item with subpage
	 */
	public function register_menu() {
		add_menu_page(
			__( 'Transcode Queue', 'mpp-local-transcoder' ),
			__( 'Transcoder', 'mpp-local-transcoder' ),
			'manage_options',
			'mpplt-queue',
			array( $this, 'render' ),
			'dashicons-flag',
			76
		);

		add_submenu_page(
			'mpplt-queue',
			__( 'Queue', 'mpp-local-transcoder' ),
			__( 'Queue', 'mpp-local-transcoder' ),
			'manage_options',
			'mpplt-queue',
			array( $this, 'render' )
		);

		add_submenu_page(
			'mpplt-queue',
			__( 'History', 'mpp-local-transcoder' ),
			__( 'History', 'mpp-local-transcoder' ),
			'manage_options',
			'mpplt-log',
			array( $this, 'render' )
		);

		add_submenu_page(
			'mpplt-queue',
			__( 'Bulk Process', 'mpp-local-transcoder' ),
			__( 'Bulk Process', 'mpp-local-transcoder' ),
			'manage_options',
			'mpplt-bulk-process',
			array( $this, 'render' )
		);
	}

	/**
	 * Initialize settings
	 */
	public function init() {
		$this->handle();
	}

	/**
	 * Add links to plugin entry in plugin list.
	 *
	 * @param array $actions actions.
	 *
	 * @return array
	 */
	public function plugin_action_links( $actions ) {

		if ( is_super_admin() ) {
			$actions['view-mpplt-queue'] = sprintf( '<a href="%1$s" title="%2$s">%3$s</a>', admin_url( 'admin.php?page=mpplt-queue' ), __( 'Queue', 'mpp-local-transcoder' ), __( 'Queue', 'mpp-local-transcoder' ) );
		}

		$actions['view-mpplt-docs'] = sprintf( '<a href="%1$s" title="%2$s" target="_blank">%2$s</a>', 'https://buddydev.com/docs/mpp-local-transcoder/getting-started-with-mediapress-local-encoder/', __( 'Documentation', 'mpp-local-transcoder' ) );

		return $actions;
	}

	/**
	 * Setup.
	 */
	private function handle() {
		$this->parse_vars();

		$slug = $this->page_slug;

		switch ( $slug ) {
			case 'mpplt-queue':
			default:
			    $this->process_queue_bulk_action();
				$this->handle_queue();
				break;

			case 'mpplt-log':
				$this->process_log_bulk_action();
				$this->handle_log();
				break;
		}
	}

	/**
	 * Handle screens for queue items table.
	 */
	private function handle_queue() {
		$this->table_type = 'queue';
	}

	/**
	 * Handle screens for log items table.
	 */
	private function handle_log() {

		if ( empty( $this->log_id ) ) {
			$this->table_type = 'log';
		} elseif ( $this->log_id && 'retry-entry' == $this->action ) {
			$this->add_to_queue();
		}
	}

	/**
	 * Render.
	 */
	public function render() {
		$slug = $this->page_slug;

		switch ( $slug ) {
			case 'mpplt-log':
				if ( ! $this->log_id ) {
					$this->render_log();
				}
				break;

			case 'mpplt-bulk-process':
				$this->render_bulk_process();
				break;

			default:
				$this->render_queue();
				break;
		}
	}

	/**
	 * Render queue screen.
	 */
	private function render_queue() {

		if ( ! $this->table_type ) {
			wp_die( esc_html__( 'Invalid action.', 'mpp-local-transcoder' ) );
		}

		require mpplt_local_transcoder()->path . 'src/admin/templates/queue/queue.php';
	}


	/**
	 * Render logs page.
	 */
	private function render_log() {

		if ( ! $this->table_type ) {
			wp_die( esc_html__( 'Invalid action.', 'mpp-local-transcoder' ) );
		}

		require mpplt_local_transcoder()->path . 'src/admin/templates/logs/log.php';
	}

	/**
	 * Render bulk process screen
	 */
	private function render_bulk_process() {
		require_once dirname( __FILE__ ) . '/templates/bulk-process.php';
	}

	/**
	 * Parse various vars.
	 */
	private function parse_vars() {
	    // phpcs:disable
		$this->page_slug = isset( $_GET['page'] ) ? trim( $_GET['page'] ) : '';
		$this->log_id    = isset( $_GET['log_id'] ) ? absint( $_GET['log_id'] ) : 0;
		$this->action    = isset( $_GET['mpplt_action'] ) ? trim( $_GET['mpplt_action'] ) : '';
		$this->hidden    = empty( $_GET['mpplt_state'] ) ? 0 : 1;
		$this->message   = isset( $_GET['message'] ) ? esc_html( $_GET['message'] ) : '';
		// phpcs:enable
	}

	/**
	 * Get current state vars.
	 *
	 * @return array
	 */
	private function get_vars() {
		return array(
			'page'   => $this->page_slug,
			'log_id' => $this->log_id,
			'action' => $this->action,
			'hidden' => $this->hidden,
		);
	}

	/**
	 * Hidden field for page slug
	 */
	private function hidden_fields() {
		?>
        <input type="hidden" name="page" value="<?php echo esc_attr( $this->page_slug ); ?>"/>
		<?php
	}

	/**
	 * Add error log item to queue again for retry
	 */
	private function add_to_queue() {
		$queue = Queue::find( $this->log_id );

		if ( empty( $queue ) || 'error' != $queue->status ) {
			return;
		}

		if ( $queue->process_id && MPPLT_Process::is_running( $queue->process_id ) ) {
			MPPLT_Process::kill( $queue->process_id );
		}

		if ( 'conversion' == $queue->queue_type ) {
			mpp_delete_media_meta( $queue->media_id, '_mpplt_encoded' );
		} elseif ( 'preview_generation' == $queue->queue_type ) {
			mpp_delete_media_meta( $queue->media_id, '_mpplt_preview_generated' );
		}

		$add = mpplt_add_item(
			array(
				'media_id'   => $queue->media_id,
				'queue_type' => $queue->queue_type,
			)
		);

		if ( ! $add ) {
			$args = array(
				'message_type' => 'error',
				'message'      => __( 'No able to add item to queue', 'mpp-local-transcoder' ),
			);
		} else {
			$args = array(
				'message_type' => 'success',
				'message'      => __( 'Item is added to the queue', 'mpp-local-transcoder' ),
			);
		}

		mpplt_redirect( mpplt_admin_get_log_url( $args ) );
	}

	/**
	 * Process bulk actions
	 */
	public function process_queue_bulk_action() {
		$table = new Queue_Items_Table();

		if ( 'delete' !== $table->current_action() ) {
			return;
		}
		// In our file that handles the request, verify the nonce.
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-queue_items' ) ) {
			die( esc_html__( 'Invalid action', 'mpp-local-transcoder' ) );
		}

		$ids = empty( $_GET['id'] ) ? array() : wp_parse_id_list( $_GET['id'] );

		foreach ( $ids as $id ) {
		    $queue = Queue::find( $id );

		    if ( MPPLT_Process::is_running( $queue->process_id ) ) {
		        MPPLT_Process::kill( $queue->process_id );
            }
        }

		$deleted = Queue::destroy(
			array(
				'id' => array(
					'op'    => 'IN',
					'value' => $ids,
				),
			)
		);

		if ( ! $deleted ) {
			$args = array(
				'message_type' => 'error',
				'message'      => __( 'Something went wrong', 'mpp-local-transcoder' ),
			);
		} else {
			$args = array(
				'message_type' => 'success',
				'message'      => __( 'Items deleted!', 'mpp-local-transcoder' ),
			);
		}

		mpplt_redirect( mpplt_admin_get_queue_url( $args ) );
	}

	/**
	 * Process bulk actions
	 */
	public function process_log_bulk_action() {
		$table = new Log_Items_Table();

		if ( 'delete' !== $table->current_action() ) {
			return;
		}
		// In our file that handles the request, verify the nonce.
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-log_items' ) ) {
			die( esc_html__( 'Invalid action', 'mpp-local-transcoder' ) );
		}

		$ids = empty( $_GET['id'] ) ? array() : wp_parse_id_list( $_GET['id'] );

		$deleted = Queue::destroy(
			array(
				'id' => array(
					'op'    => 'IN',
					'value' => $ids,
				),
			)
		);

		if ( ! $deleted ) {
			$args = array(
				'message_type' => 'error',
				'message'      => __( 'Something went wrong', 'mpp-local-transcoder' ),
			);
		} else {
			$args = array(
				'message_type' => 'success',
				'message'      => __( 'Items deleted!', 'mpp-local-transcoder' ),
			);
		}

		mpplt_redirect( mpplt_admin_get_log_url( $args ) );
	}

	/**
	 * Get the table.
	 *
	 * @return Log_Items_Table|Queue_Items_Table|null
	 */
	private function get_table() {
		$table = null;

		switch ( $this->table_type ) {

			case 'queue':
				$table = new Queue_Items_Table( $this->get_vars() );
				break;
			case 'log':
				$table = new Log_Items_Table( $this->get_vars() );
				break;
		}

		return $table;
	}
}
