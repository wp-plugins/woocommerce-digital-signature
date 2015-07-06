<?php
/**
 * 
 * @package ESIG_WOOCOMMERCE
 * @author  Approve me <abushoaib73@gmail.com>
 */

if ( ! defined( 'ABSPATH' ) ) { 
	exit; // Exit if accessed directly
}

if (!class_exists('ESIG_WOOCOMMERCE')) :
class ESIG_WOOCOMMERCE {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.1
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';
	
	

	/**
	 *
	 * Unique identifier for plugin.
	 *
	 * @since     0.1
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'esig-woocommerce';

	/**
	 * Instance of this class.
	 *
	 * @since     1.0.1
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     0.1
	 */
	private function __construct() {

		add_filter('esig-system-requirement',array($this,'esig_woo_requirement_msg'),10,1);
		
		add_action( 'admin_init',array($this, 'esign_woo_after_install') );
		
		add_filter( 'plugin_row_meta', array($this,'about_page_action_link'),10,2 );
	}
	
	
	
	public function about_page_action_link( $links, $file ) {
	  
	    
	    if ( strpos( $file, 'woocommerce-esig.php' ) !== false ) {
	        $new_links = array(
	            '<a href="'. get_admin_url(null, 'index.php?page=esign-woocommerce-about') .'">'. __('Need help getting started?','esig-woocommerce') .'</a>'
	        );
	    
	        $links = array_merge( $links, $new_links );
	    }
	    
	    return $links;
	    
	}
	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     0.1
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		$screen = get_current_screen();
		$admin_screens = array(
			'dashboard_page_esign-woocommerce-about',
			
			);
	
		if (in_array($screen->id, $admin_screens)) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'admin/assets/css/esign-woocommerce.css', __FILE__ ), array());
		}

	}
	
	public function esign_woo_after_install() 
	{
		global $pagenow;
		
		if( ! is_admin() )
		return;
		
		// Delete the transient
		//delete_transient( '_esign_activation_redirect' );
		if(delete_transient( '_esign_woo_redirect' )) 
		{
			wp_safe_redirect( admin_url( 'index.php?page=esign-woocommerce-about' ));
			exit;
		}
		
	}
	
	public function esig_woo_requirement_msg($msg)
	{
		if (!is_plugin_active( 'woocommerce/woocommerce.php' )) 
		{
			$msg .=_e('<div class="alert e-sign-red-alert alert e-sign-alert esig-updated"><p>Hi there! It looks like the <a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce plugin</a> is not active. You need to activate the WooCommerce plugin in order to use the E-signature WooCommerce add-on features.<br></p></div>','esig-woocommerce');
		}
		if (!is_plugin_active( 'esig-stand-alone-docs/esig-sad.php' )) 
		{
			$msg .=_e('<div class="alert e-sign-red-alert alert e-sign-alert esig-updated"><p>Hi there! It looks like the E-signature <a href="admin.php?page=esign-addons">Stand Alone Document</a> plugin is not active. You need to activate the Stand Alone Document add-on to use the E-signature WooCommerce Features.<br></p></div>','esig-woocommerce');
		}
		
		return $msg;
		
	}
	
	/**
	 * Returns the plugin slug.
	 *
	 * @since     0.1
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Returns an instance of this class.
	 *
	 * @since     0.1
	 * @return    object    A single instance of this class.
	 */
	 
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since     0.1
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	 
	public static function activate( $network_wide ) {
		self::single_activate();
		
		set_transient( '_esign_woo_redirect', true, 30 );	
		
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since     0.1
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		self::single_deactivate();
	}

	

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since     0.1
	 */
	private static function single_activate() {
		//@TODO: Define activation functionality here
         if(get_option('WP_ESignature__Auto_Add_My_Signature_documentation'))
        {
			update_option('WP_ESignature__woocommerce_documentation','http://wordpress.org/plugins/woocommerce-digital-signature/');
            
        }
        else
        {
           
			add_option('WP_ESignature__woocommerce_documentation','http://wordpress.org/plugins/woocommerce-digital-signature/');
        }
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since     0.1
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	
	
	
}
endif;
