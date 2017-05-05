<?php

/* Plugin Name: BuddyPress Live Notification
 * Plugin URI: https://buddydev.com/plugins/buddypress-live-notification/
 * Version: 2.0.1
 * Description: Adds a Facebook Like realtime notification for user on a BuddyPress based social network
 * Author: BuddyDev
 * Author URI: https://buddydev.com
 * License: GPL
 *
 * */


class BP_Live_Notification_Helper {
	/**
	 *
	 * @var BP_Live_Notification_Helper
	 */
	private static $instance;

	private $url;
	private $path;

	private function __construct() {

		$this->url  = plugin_dir_url( __FILE__ );
		$this->path = plugin_dir_path( __FILE__ );

		add_action( 'bp_include', array( $this, 'load' ) );

		add_action( 'bp_loaded', array( $this, 'load_textdomain' ) );

		add_action( 'bp_enqueue_scripts', array( $this, 'load_css' ) );
		add_action( 'bp_enqueue_scripts', array( $this, 'load_js' ) );

		add_action( 'wp_head', array( $this, 'add_js_global' ) );

	}

	/**
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
	 *
	 */
	public function load() {

		if ( ! $this->is_active() ) {
			return;
		}


		$files = array(
			'functions.php',
			'ajax.php',
		);


		foreach ( $files as $file ) {
			require_once $this->path . $file;
		}
	}

	/**
	 * Load translation file
	 *
	 */
	public function load_textdomain() {

		$locale = apply_filters( 'bp-live-notification_get_locale', get_locale() );


		if ( ! empty( $locale ) ) {

			$mofile = sprintf( '%slanguages/%s.mo', $this->path, $locale );

			if ( is_readable( $mofile ) ) {
				load_textdomain( 'bp-live-notification', $mofile );
			}
		}

	}

	public function get_js_settings() {

		return apply_filters( 'bpln_get_js_settings', array(
			'timeout'       => 10,
			//timeou in 10 seconds
			'last_notified' => current_time( 'mysql' )
			//please do not change last_notified as we use it to filter the new notifications
		) );
	}

	/**
	 * Load required js
	 *
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
		wp_enqueue_script( 'bpln_js' );//I am not adding achtung_js as a dependency to avoid the condition when the achtung_js will be replaced by some other library and bpln_js won't load 
	}

	/**
	 * Load CSS file
	 *
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
		?>
        <script type="text/javascript">
            var bpln = <?php echo json_encode( $this->get_js_settings() );?>;
        </script>

		<?php
	}


	public function is_active() {

		if ( bp_is_active( 'notifications' ) ) {
			return true;
		}

		return false;
	}


}

BP_Live_Notification_Helper::get_instance();