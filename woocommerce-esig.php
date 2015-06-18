<?php
/**
 * @package   	      WP E-Signature - WooCommerce
 * @contributors	  Kevin Michael Gray (Approve Me), Abu Shoaib (Approve Me)
 * @wordpress-plugin
 * Plugin Name:       WP E-Signature - WooCommerce
 * Plugin URI:        http://approveme.me/wp-digital-e-signature
 * Description:       This add-on lets you require customers sign one (or more) legally binding contracts before they can complete their WooCommerce checkout process.
 * Version:           1.2.0
 * Author:            Approve Me
 * Author URI:        http://approveme.me/
 * Text Domain:       esig-woocommerce
 * Domain Path:       /languages
 * License/Terms & Conditions: http://www.approveme.me/terms-conditions/
 * Privacy Policy: http://www.approveme.me/privacy-policy/
 */


// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) { 
	exit; // Exit if accessed directly
}


/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'includes/woocommerce-esig.php' );

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {	

	/*
	 * Register hooks that are fired when the plugin is activated or deactivated.
	 * When the plugin is deleted, the uninstall.php file is loaded.
	 */
	
	register_activation_hook( __FILE__, array( 'ESIG_WOOCOMMERCE', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'ESIG_WOOCOMMERCE', 'deactivate' ) );
   
	

	/**
	* Check if WooCommerce is active
	**/

	
	require_once( plugin_dir_path( __FILE__ ) . 'admin/woocommerce-esig-admin.php' );
	require_once( plugin_dir_path( __FILE__ ) . 'admin/woocommerce-esig-shortcode.php' );
    require_once( plugin_dir_path( __FILE__ ) . 'includes/class-esig-woocommerce-sad.php' );
	
	add_action( 'plugins_loaded', array( 'ESIG_WOOCOMMERCE_Admin', 'get_instance' ) );
	add_action( 'plugins_loaded', array( 'ESIG_WOOCOMMERCE_Shortcode', 'get_instance' ) );



	/**
	 * Load plugin textdomain.
	 *
	 * @since 1.1.3
	 */
	function esig_commerce_load_textdomain() {
		
		load_plugin_textdomain('esig-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
	}
	add_action( 'plugins_loaded', 'esig_commerce_load_textdomain');

}
else 
{
	add_action( 'plugins_loaded', array( 'ESIG_WOOCOMMERCE', 'get_instance' ) );
}