<?php
/**
 * Plugin Name:  MediaPress Local Transcoder
 * Plugin URI: https://buddydev.com/plugins/mpp-local-transcoder/
 * Description: Local transcoder using ffmpeg. This is not recommended for high traffic site.
 * Version: 1.0.1
 * Author: BuddyDev
 * Author URI: https://buddydev.com
 */

/**
 * @contributor: Brajesh Singh(sbrajesh), Ravi Sharma(raviousprime)
 */

/**
 * This transcoder is not suitable for sites having high video upload rate. Should work fine for low to moderate video count.
 */
use MPP_Local_Transcoder\Bootstrap\Autoloader;
use MPP_Local_Transcoder\Bootstrap\Bootstrapper;
use MPP_Local_Transcoder\Cron\MPPLT_Task_Scheduler;
use MPP_Local_Transcoder\Schema\Schema;

// Do not allow direct access over web.
defined( 'ABSPATH' ) || exit;

/**
 * Class MPPLT_Local_Transcoder
 *
 * @property-read string $path     Absolute path to the plugin directory.
 * @property-read string $url      Absolute url to the plugin directory.
 * @property-read string $basename Plugin base name.
 * @property-read string $version  Plugin version.
 */
class MPPLT_Local_Transcoder {

	/**
	 * Plugin Version.
	 *
	 * @var string
	 */
	private $version = '1.0.1';

	/**
	 * Singleton instance
	 *
	 * @var static
	 */
	private static $instance = null;

	/**
	 * Plugins directory path
	 *
	 * @var string
	 */
	private $path;

	/**
	 * Plugins directory url
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Plugin Basename.
	 *
	 * @var string
	 */
	private $basename;

	/**
	 * Protected properties. These properties are inaccessible via magic method.
	 *
	 * @var array
	 */
	private static $protected = array( 'instance' );

	/**
	 * The constructor.
	 */
	private function __construct() {
		$this->bootstrap();
		$this->setup();
	}

	/**
	 * Get class instance
	 *
	 * @return MPPLT_Local_Transcoder
	 */
	public static function get_instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Bootstrap the core.
	 */
	private function bootstrap() {
		// Setup general properties.
		$this->path     = plugin_dir_path( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->basename = plugin_basename( __FILE__ );

		// Load autoloader.
		require_once $this->path . 'src/bootstrap/class-autoloader.php';

		// Register autoloader.
		spl_autoload_register( new Autoloader( 'MPP_Local_Transcoder\\', __DIR__ . '/src/' ) );

		register_activation_hook( __FILE__, array( $this, 'on_activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'on_deactivation' ) );
	}

	/**
	 * Load plugin core files and assets.
	 */
	private function setup() {
		Bootstrapper::boot();
	}

	/**
	 * On activation create table
	 */
	public function on_activation() {

		if ( ! get_option( 'mpplt-settings' ) ) {
			// Update default settings on activation.
		}

		// Cron scheduling.
		MPPLT_Task_Scheduler::boot();
		MPPLT_Task_Scheduler::on_activation();

		Schema::create();

		if ( ! get_option( 'mpplt_db_version' ) ) {
			Schema::alter();
			update_option( 'mpplt_db_version', 1 );
		}
	}

	/**
	 * On deactivation clear cron jobs scheduled by plugin.
	 */
	public function on_deactivation() {
		MPPLT_Task_Scheduler::on_deactivation();
	}

	/**
	 * Magic method for accessing property as readonly(It's a lie, references can be updated).
	 *
	 * @param string $name property name.
	 *
	 * @return mixed|null
	 */
	public function __get( $name ) {

		if ( ! in_array( $name, self::$protected, true ) && property_exists( $this, $name ) ) {
			return $this->{$name};
		}

		return null;
	}
}

/**
 * Return object of class
 *
 * @return MPPLT_Local_Transcoder
 */
function mpplt_local_transcoder() {
	return MPPLT_Local_Transcoder::get_instance();
}

mpplt_local_transcoder();
