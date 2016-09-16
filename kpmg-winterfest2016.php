<?php
/**
 * Plugin Name: KPMG Winterfest 2016
 * Plugin URI: http://ojambo.com
 * Description: KPMG Winterfest 2016 Wordpress plugin
 * Author: Edward Ojambo
 * Author URI: http://ojambo.com
 * Version: 1.0.0
 * License: GPL2
 * 
 * Text Domain:     ojambo.com
 *
 * @package         ojambo-giftcards
 * @author          Edward Ojambo
 * @copyright       Copyright (c) 2016
 *
 * File: kpmg-winterfest2016.php
 * Description of kpmg-winterfest2016
 *
 * Author: edward <http://ojambo.com>
 * Copyright: 2016  
 * Created : 2016-08-26 12:39:41 PM
 * Last Modified : 2016-08-26T16:39:41Z
 */

// Display Errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Check If Class Exists Before Creating It
if( !class_exists( 'kpmgwinterfest2016' ) )
{
	class kpmgwinterfest2016
	{
        /**
         * @var         kpmgwinterfest2016 $instance The one true kpmgwinterfest2016
         * @since       1.0.0
         */		
		private static $instance;

        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true kpmgwinterfest2016
         */		
		
        public static function instance() {
            if( !self::$instance ) {
                self::$instance = new kpmgwinterfest2016();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->hooks();
            }

            return self::$instance;
        }
		
		
        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {
            define( 'KPMGWF_VERSION',   '1.0.0' ); // Plugin version
            define( 'KPMGWF_DIR',       plugin_dir_path( __FILE__ ) ); // Plugin Folder Path
            define( 'KPMGWF_URL',       plugins_url( 'kpmg-winterfest2016', 'kpmgwinterfest2016.php' ) ); // Plugin Folder URL
            define( 'KPMGWF_FILE',      plugin_basename( __FILE__ )  ); // Plugin Root File
            define( 'KPMGWF_Site',      site_url() ); // Site URL
            define( 'KPMGWF_Info',      site_url('/my-info') ); // MyInfo URL
            define( 'KPMGWF_Admin',      site_url('/administration') ); // Administration URL
            define( 'KPMGWF_ForgotPassword',      site_url('/login/password-reminder') ); // Forgot Password URL
            define( 'KPMGWF_Login',      site_url('/login') ); // Login URL
            define( 'KPMGWF_LogoutRedirect',      site_url() ); // Logout Redirect URL
            define( 'KPMGWF_Register',      site_url('/register') ); // Register URL
            define( 'KPMGWF_Register1',      site_url('/register/step-1') ); // Register Step 1 URL
            define( 'KPMGWF_Register2',      site_url('/register/step-2') ); // Register Step 2 URL
            define( 'KPMGWF_Register3',      site_url('/register/step-3') ); // Register Step 3 URL
            define( 'KPMGWF_RegisterTY',      site_url('/register/thank-you') ); // Register Thanks URL
            define( 'KPMGWF_Group',      site_url('/group') ); // Group URL
            define( 'KPMGWF_GroupTY',      site_url('/group/thanks') ); // Group Thanks URL
            define( 'KPMGWF_AdminRole',      'winterfest_admin' ); // Admin Role
            define( 'KPMGWF_EmployeeRole',      'winterfest_employee' ); // Employee Role
            define( 'KPMGWF_Salt',      'ojambo' ); // Salt
            define( 'KPMGWF_LoginAttempts',      7 ); // Login Attempts
            define( 'KPMGWF_MinGroupSeats',      2 ); // Minimum Group Seats
            define( 'KPMGWF_MaxGroupSeats',      10 ); // Maximum Group Seats
        }		
		
        /**
         * Include necessary files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {
			// Include Wordpress Scripts
			require_once(ABSPATH.'wp-admin/includes/user.php' );
			
            // Include scripts
            require_once KPMGWF_DIR . 'includes/data.php';
            require_once KPMGWF_DIR . 'includes/functions.php';
            require_once KPMGWF_DIR . 'includes/ajax.php';
			
			if( ! class_exists( 'KPMG_Login' ) ) {
                require_once KPMGWF_DIR . 'includes/class.login.php';
            }
			
			if( ! class_exists( 'KPMG_Employee' ) ) {
                require_once KPMGWF_DIR . 'includes/class.employee.php';
				//new KPMG_Employee();
            }
			
			
			if( ! class_exists( 'KPMG_Admin' ) ) {
                require_once KPMGWF_DIR . 'includes/class.admin.php';
				//new KPMG_Admin();
            }
			
			if( ! class_exists( 'KPMG_Email' ) ) {
                require_once KPMGWF_DIR . 'includes/class.email.php';
				//new KPMG_Email();
            }

            if( ! class_exists( 'KPMG_ShortCodes' ) ) {
                require_once KPMGWF_DIR . 'includes/class.shortcodes.php';
				new KPMG_ShortCodes(); // Also instances of other classes
            }
			


        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         *
         */
        private function hooks() {
            // Register settings
            $ojr_woo_giftcard_settings = get_option( 'kpmg_wf_options' );

            //add_filter( 'plugin_action_links_' . KPMGWF_FILE, array( __CLASS__, 'plugin_action_links' ) );

        }		
	}
} // End if class_exists check


/**
 * The main function responsible for returning the one true WPRWooGiftcards
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \ojambogiftcards The one true ojambogiftcards
 *
 */
function kpmgwinterfest2016_load() 
{
	return kpmgwinterfest2016::instance();

}
add_action( 'plugins_loaded', 'kpmgwinterfest2016_load' );


/**
 * The activation hook is called outside of the singleton because WordPress doesn't
 * register the call from within the class, since we are preferring the plugins_loaded
 * hook for compatibility, we also can't reference a function inside the plugin class
 * for the activation function. If you need an activation function, put it here.
 *
 * @since       1.0.0
 * @return      void
 */
function kpmg_winterfest2016_activation() {
    /* Activation functions here */
}
register_activation_hook( __FILE__, 'kpmg_winterfest2016_activation' );