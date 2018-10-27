<?php
/**
 * Plugin Name: BuddyPress Live Notification
 * Plugin URI: https://buddydev.com/plugins/buddypress-live-notification/
 * Version: 2.1.0
 * Description: Adds a Facebook Like realtime notification for user on a BuddyPress based social network
 * Author: BuddyDev
 * Author URI: https://buddydev.com
 * License: GPL
 */

// No direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

/**
 * Main Class.
 */
class BP_Live_Notification_Helper {

	/**
	 * Singleton instance.
	 *
	 * @var BP_Live_Notification_Helper
	 */
	private static $instance;

	/**
	 * Plugin Directory url.
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Plugin director path.
	 *
	 * @var string
	 */
	private $path;

	/**
	 * Constructor.
	 */
	private function __construct() {

		$this->url  = plugin_dir_url( __FILE__ );
		$this->path = plugin_dir_path( __FILE__ );

		add_action( 'bp_include', array( $this, 'load' ) );

		add_action( 'bp_loaded', array( $this, 'load_translations' ) );

		add_action( 'bp_enqueue_scripts', array( $this, 'load_css' ) );
		add_action( 'bp_enqueue_scripts', array( $this, 'load_js' ) );

		add_action( 'wp_head', array( $this, 'add_js_global' ) );
	}

	/**
	 * Get the singleton.
	 *
	 * @return BP_Live_Notification_Helper
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Load core files
	 */
	public function load() {

		if ( ! $this->is_active() ) {
			return;
		}

		$files = array(
			'core/bp-live-notifications-functions.php',
			'core/bp-live-notifications-ajax-handler.php',
		);

		foreach ( $files as $file ) {
			require_once $this->path . $file;
		}
	}

	/**
	 * Load translation file
	 */
	public function load_translations() {
		load_plugin_textdomain( 'bp-live-notification', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Get js variables.
	 *
	 * @return array
	 */
	public function get_js_settings() {
		return apply_filters( 'bpln_get_js_settings', array(
			// timeout in 10 seconds.
			'timeout'       => 10,
			// please do not change last_notified as we use it to filter the new notifications.
			'last_notified' => bpln_get_latest_notification_id(),
		) );
	}

	/**
	 * Load required js
	 */
	public function load_js() {

		if ( ! $this->is_active() ) {
			return;
		}

		if ( ! is_user_logged_in() || is_admin() && bpln_disable_in_dashboard() ) {
			return;
		}

		wp_register_script( 'achtung_js', $this->url . 'assets/vendors/achtung/ui.achtung.min.js', array( 'jquery' ) );

		wp_register_script( 'bpln_js', $this->url . 'assets/js/bpln.js', array( 'jquery', 'json2', 'heartbeat' ) );

		wp_enqueue_script( 'achtung_js' );
		wp_enqueue_script( 'bpln_js' );// I am not adding achtung_js as a dependency to avoid the condition when the achtung_js will be replaced by some other library and bpln_js won't load.
	}

	/**
	 * Load CSS file
	 */
	public function load_css() {

		if ( ! $this->is_active() ) {
			return;
		}

		if ( ! is_user_logged_in() || is_admin() && bpln_disable_in_dashboard() ) {
			return;
		}

		wp_register_style( 'achtung_css', $this->url . 'assets/vendors/achtung/ui.achtung.css' );
		wp_enqueue_style( 'achtung_css' );
	}

	/**
	 * Add global bpln object
	 */
	public function add_js_global() {
		if ( ! $this->is_active() ) {
			return;
		}

		?>
        <script type="text/javascript">
            var bpln = <?php echo json_encode( $this->get_js_settings() );?>;
        </script>

		<?php
	}

	/**
	 * Is BuddyPress Notifications active.
	 *
	 * @return bool
	 */
	public function is_active() {

		if ( function_exists( 'bp_is_active' ) && bp_is_active( 'notifications' ) ) {
			return true;
		}

		return false;
	}
}

BP_Live_Notification_Helper::get_instance();
