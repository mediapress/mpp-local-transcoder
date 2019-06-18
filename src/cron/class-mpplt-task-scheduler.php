<?php
/**
 * MediaPress Local Transcoder Task Scheduler.
 *
 * @package    MPP Local Transcoder
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace MPP_Local_Transcoder\Cron;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Scheduler
 */
class MPPLT_Task_Scheduler {

	/**
	 * Boot this class.
	 */
	public static function boot() {
		$self = new self();
		$self->setup();

		return $self;
	}

	/**
	 * Setup hooks callbacks
	 */
	public function setup() {
		add_filter( 'cron_schedules', array( $this, 'schedules' ) );
	}

	/**
	 * Add our schedules.
	 *
	 * @param array $schedules cron schedules.
	 *
	 * @return mixed
	 */
	public function schedules( $schedules ) {
		$schedules['mpplt_1min'] = array(
			'interval' => 60,
			'display'  => __( 'Once per minute.', 'mpp-local-transcoder' ),
		);

		$schedules['mpplt_5min'] = array(
			'interval' => 300,
			'display'  => __( 'Once per 5 minutes.', 'mpp-local-transcoder' ),
		);

		return $schedules;
	}

	/**
	 * Setup on activation.
	 */
	public static function on_activation() {

		if ( ! wp_next_scheduled( 'mpplt_process_queue' ) ) {
			wp_schedule_event( time(), 'mpplt_1min', 'mpplt_process_queue' );
		}

		if ( ! wp_next_scheduled( 'mpplt_process_queue_preview_generation' ) ) {
			wp_schedule_event( time(), 'mpplt_1min', 'mpplt_process_queue_preview_generation' );
		}
	}

	/**
	 * Setup on activation.
	 */
	public static function on_deactivation() {
		wp_clear_scheduled_hook( 'mpplt_process_queue' );
		wp_clear_scheduled_hook( 'mpplt_process_queue_preview_generation' );
	}
}
