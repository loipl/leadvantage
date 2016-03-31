<?php
/*
Plugin Name: WP Last Activity
Plugin URI: http://yoda.neun12.de 
Description: Show last activity of any users on the user screen
Version: 1.1 MODIFIED
Author: Ralf Albert, Eric Teubert
Author URI: neun12@googlemail.com, ericteubert@googlemail.com 
License: GPL
*/

/*
 * Based on a idea from Eric Teubert
 * see http://www.satoripress.com/2011/12/wordpress/plugin-development/find-users-by-last-login-activity-225/
 */

if( ! function_exists( 'add_action' ) )
	die( __( 'Cheating uh?' ) );

if( ! class_exists( 'WP_Last_Activity' ) ){
	add_action( 'plugins_loaded', array( 'WP_Last_Activity', 'start_plugin' ) );
	
	class WP_Last_Activity
	{
		/**
		 * 
		 * Textdomain
		 * @var const string WP_LAST_ACTIVITY
		 */
		const WP_LAST_ACTIVITY = 'wp_last_activity';
		
		/**
		 * 
		 * Plugin-instance
		 * @var object $plugin_self
		 */
		private static $plugin_self = NULL;
		
		/**
		 * 
		 * Constructor
		 * Add all needed filter & actions
		 * 
		 * @param none
		 * @return void
		 * @since 1.0
		 */
		public function __construct(){
			// prepare plugin on activation
//			register_activation_hook( __FILE__, array( &$this, 'add_last_activity_for_all_users' ) );
			
			//TODO: deactivation-hook and uninstall-hook
			// register_deactivation_hook( $file, $function );
			// register_uninstall_hook( $file, $callback );
			
			// filter & hooks for logging
			add_action( 'wp_login' , array( &$this, 'add_login_time' ), 10, 1 );
			add_action( 'auth_cookie_valid', array( &$this, 'stay_logged_in_users' ), 10, 1 );

		}

		/**
		 * 
		 * Plugin start
		 * Create an instance of the plugin
		 * 
		 * @param none
		 * @return object $plugin_self
		 * @since 1.0
		 */
		public static function start_plugin(){
			if( NULL == self::$plugin_self )
				self::$plugin_self = new self;
			
			return self::$plugin_self;
		} 
		
		/**
		 * 
		 * Logging the date & time a user log in
		 * 
		 * @param string $user_login
		 * @return void
		 * @since 1.0
		 */
		public function add_login_time( $user_login ) {
			$user = get_user_by( 'login', $user_login );
			update_user_meta( $user->ID, 'last_activity', current_time( 'mysql' ) );
		}

		/**
		 * 
		 * Handles users who log in with cookie (stay logged in users)
		 * 
		 * @param array $user Array with userdata
		 * @return void
		 * @uses add_login_time()
		 * @since 1.0
		 */
		public function stay_logged_in_users( $user ){
			//TODO: add session-value to avoid logging on each request
			$this->add_login_time( $user['username'] );
		}

		/**
		 * 
		 * Add the current date & time to all users when the plugin is activated
		 * 
		 * @param none
		 * @return void
		 * @since 1.0
		 */
		public function add_last_activity_for_all_users() {
			global $wpdb;
			
			$sql = $wpdb->prepare( "
				SELECT
					u.ID
				FROM
					$wpdb->users AS u
					LEFT JOIN $wpdb->usermeta m ON u.ID = m.user_id AND m.meta_key = 'last_activity'
				WHERE
					m.meta_value IS NULL" );
			$userids = $wpdb->get_col( $sql );
			
			if ( $userids ) {
				foreach ( $userids as $userid ) {
					update_user_meta( $userid, 'last_activity', current_time( 'mysql' ) );
				}
			}
		}

	} // .class WP_Last_activity
} // .if-class-exists
