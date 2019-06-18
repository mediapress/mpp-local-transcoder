<?php
/**
 * Assets Loader
 *
 * @package    MPP Local Transcoder
 * @subpackage Bootstrap
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace MPP_Local_Transcoder\Bootstrap;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

/**
 * Assets Loader.
 */
class Assets_Loader {

	/**
	 * Data to be send as localized js.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Boot it.
	 */
	public static function boot() {
		$self = new self();

		add_action( 'admin_enqueue_scripts', array( $self, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Register assets.
	 */
	public function register() {
		// $this->register_vendors();
		$this->register_core();
	}

	/**
	 * Load assets.
	 */
	public function enqueue() {
		wp_enqueue_style( 'mpp_local_transcoder_css' );

		wp_enqueue_script( 'mpp_local_transcoder_js' );
		wp_localize_script( 'mpp_local_transcoder_js', 'MPPLocalTranscoder', $this->data );
	}

	/**
	 * Register vendor scripts.
	 */
	private function register_vendors() {
	}

	/**
	 * Register core assets.
	 */
	private function register_core() {
		$plugin_dir_url = mpplt_local_transcoder()->url;
		$plugin_version = mpplt_local_transcoder()->version;

		wp_register_style(
			'mpp_local_transcoder_css',
			$plugin_dir_url . 'assets/css/mpp-local-transcoder-admin.css',
			array(),
			$plugin_version
		);

		wp_register_script(
			'mpp_local_transcoder_js',
			$plugin_dir_url . 'assets/js/mpp-local-transcoder-admin.js',
			array( 'jquery' ),
			$plugin_version,
			false
		);

		$this->data = array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) );
	}

	/**
	 * Load admin css.
	 */
	public function admin_enqueue_scripts() {
		$this->register();
		$this->enqueue();
	}
}