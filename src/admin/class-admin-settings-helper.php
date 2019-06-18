<?php
/**
 * Admin settings helper class
 *
 * @package    MPP Local Transcoder
 * @subpackage Admin
 * @copyright  Copyright (c) 2019, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace MPP_Local_Transcoder\Admin;

// No direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Settings_Helper
 */
class Admin_Settings_Helper {

	/**
	 * Boot class
	 */
	public static function boot() {
		$self = new self();
		$self->setup();
	}

	/**
	 * Callback to actions
	 */
	private function setup() {
		add_action( 'mpp_admin_register_settings', array( $this, 'register_settings' ) );
	}

	/**
	 * Register settings
	 *
	 * @param \MPP_Admin_Settings_Page $page Admin setting page object.
	 */
	public function register_settings( $page ) {
		$panel = $page->get_panel( 'addons' );

		$section = $panel->add_section( 'mpplt-settings', __( 'MediaPress Local Transcoder Settings', 'mpp-local-transcoder' ) );

		$section->add_field(
			array(
				'name'  => 'mpplt_default_thumbnail',
				'type'  => 'image',
				'label' => __( 'Default thumbnail image', 'mpp-local-transcoder' ),
			)
		);
	}
}