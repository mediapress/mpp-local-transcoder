<?php
/**
 * Bootstrapper. Initializes the plugin.
 *
 * @package    MPP Local Transcoder
 * @subpackage Bootstrap
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace MPP_Local_Transcoder\Bootstrap;

use MPP_Local_Transcoder\Admin\Admin_Loader;
use MPP_Local_Transcoder\Admin\Admin_Settings_Helper;
use MPP_Local_Transcoder\Cron\MPPLT_Task_Runner;
use MPP_Local_Transcoder\Cron\MPPLT_Task_Scheduler;
use MPP_Local_Transcoder\Handlers\MPPLT_Action_Handler;
use MPP_Local_Transcoder\Handlers\MPPLT_Ajax_Handler;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Bootstrapper.
 */
class Bootstrapper {

	/**
	 * Setup the bootstrapper.
	 */
	public static function boot() {
		$self = new self();
		$self->setup();
	}

	/**
	 * Bind hooks
	 */
	private function setup() {
		// called after pp_loaded.
		add_action( 'plugins_loaded', array( $this, 'load' ) );
		add_action( 'init', array( $this, 'load_translations' ) );

		Assets_Loader::boot();
	}

	/**
	 * Load core functions/template tags.
	 * These are non auto loadable constructs.
	 */
	public function load() {
		$this->load_common();
		$this->load_admin();
	}

	/**
	 * Load translations.
	 */
	public function load_translations() {
		load_plugin_textdomain( 'mpp-local-transcoder', false, basename( mpplt_local_transcoder()->path ) . '/languages' );
	}

	/**
	 * Load files common to each request type.
	 */
	private function load_common() {
		$path = mpplt_local_transcoder()->path;

		$files = array(
			'src/core/mpplt-functions.php',
			'src/admin/mpplt-admin-functions.php',
		);

		foreach ( $files as $file ) {
			require_once $path . $file;
		}

		MPPLT_Action_Handler::boot();
		MPPLT_Task_Scheduler::boot();
		MPPLT_Task_Runner::boot();
		MPPLT_Ajax_Handler::boot();
	}

	/**
	 * Load admin.
	 */
	private function load_admin() {

		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			Admin_Loader::boot();
			Admin_Settings_Helper::boot();
		}
	}
}
