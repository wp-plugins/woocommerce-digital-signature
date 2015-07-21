<?php
/**
 *
 * @package ESIG_WOOCOMMERCE_Shortcode
 * @author  Abu Shoaib <abushoaib73@gmail.com>
 */

if ( ! defined( 'ABSPATH' ) ) { 
	exit; // Exit if accessed directly
}

if (! class_exists('ESIG_WOOCOMMERCE_Shortcode')) :
class ESIG_WOOCOMMERCE_Shortcode {

	/**
	 * Instance of this class.
	 * @since    1.0.1
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 * @since    1.0.1
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 * @since     0.1
	 */
	private function __construct() {

		/*
		 * Call $plugin_slug from public plugin class.
		 */
		$plugin = ESIG_WOOCOMMERCE::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();
		$this->esig_sad = new esig_woocommerce_sad();
		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __FILE__ ) . $this->plugin_slug . '.php' );
		
		//adding shortcode
		
		add_shortcode( 'esig-woo-order-details', array($this, 'esig_order_details'));
		
		//adding metabox hook here
		add_action( 'add_meta_boxes', array( $this, 'esig_woo_add_meta_box' ) );
		// triggering when woo product saveed
		add_action( 'save_post', array( $this, 'esig_woo_product_save'));
		
	}
	
	/**
	 * Save the meta when the woo product is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function esig_woo_product_save($post_id)
	{
		
		// Check if our nonce is set.
		if ( ! isset( $_POST['esig_woo_product_box_nonce'] ) )
					return $post_id;
			
		$nonce = $_POST['esig_woo_product_box_nonce'];
		
		// Verify that the nonce is valid.
		if (!wp_verify_nonce( $nonce, 'esig_woo_product_nonce'))
						return $post_id;
				
		// If this is an autosave, our form has not been submitted,
		// so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
						return $post_id;
					
		
		// Check if post type is not product page return 
		if ( 'product' != $_POST['post_type'] ) 
					return $post_id;
		
		// check the user permission 
		if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;
			
			
		// Sanitize the user input.
		$esig_product_agreement = sanitize_text_field( $_POST['esig_product_agreement'] );
		$esign_woo_sad_page = sanitize_text_field( $_POST['esign_woo_sad_page'] );
		
		// Update the meta field.
		update_post_meta( $post_id, '_esig_woo_meta_product_agreement',$esig_product_agreement);
		
		update_post_meta( $post_id, '_esig_woo_meta_sad_page',$esign_woo_sad_page);
		
	}
	
	public function esig_woo_add_meta_box($post_type)
	{
		$post_types = array('product');     //limit meta box to certain post types
		if ( in_array( $post_type, $post_types )) 
		{
			add_meta_box(
				'E-signature Option'
				,__( 'Esignature Option', 'esig-woocommerce' )
				,array( $this, 'esig_woo_render_meta_box_content' )
				,$post_type
				,'side'
				,'low'
				);
		}
	}
	
	
	public function esig_woo_render_meta_box_content($post)
	{
		if(!function_exists('WP_E_Sig'))
		{
			__('<a href="admin.php?page=esign-not-core">E-signature</a>','esig-woocommerce');
			return;
		}
				
		
		$esig = WP_E_Sig();
		$api = $esig->shortcode;
		
		
		
		$branding_template = dirname(__FILE__) ."/views/woocommerce-esig-product-view.php";
		
		$template_data = get_object_vars($post);
		
		$api->view->renderPartial('', $template_data, true, '', $branding_template);
	}
	
	public function esig_order_details($atts)
	{
		if(!function_exists('WP_E_Sig'))
				return;
		
			
			$esig = WP_E_Sig();
			$api = $esig->shortcode;
			
			global $woocommerce;
			
		extract(shortcode_atts(array(
			
			), $atts, 'esig-woo-order-details'));

		$invitation_id=null;
		
		if(isset($_GET['invite']))
		 {
			$invite_hash=isset($_GET['invite'])?$_GET['invite']:null;
			$invitation= $api->invite->getInviteBy('invite_hash',$invite_hash);	
		 }
		 
		 if(isset($_GET['did']))
		 {
		 	$document_id=$api->document->document_id_by_csum($_GET['did']);
		 	$invitation= $api->invite->getInviteBy('document_id',$document_id);	
		 }

		 if(isset($_GET['esigpreview']))
		 {
				$document_id=isset($_GET['document_id'])?$_GET['document_id']:null;	
				$invitation= $api->invite->getInviteBy('document_id',$document_id);	
		 }
		 
		 if(get_option('esig_global_document_id'))
		 {
			   $document_id=get_option('esig_global_document_id');	
			  
			   $invitation= $api->invite->getInviteBy('document_id',$document_id);
			   
		 }
		 
		 if(!isset($invitation))
		 {
		 	return false ;
		 }
		
		if($invitation)
		{
			 $invitation_id =$invitation->invitation_id ; 
			
			$order_id = $api->setting->get_generic('esig-order-id'.$invitation_id);
			
			if(!$order_id)
			{
				return false ; 
			}
			$template_data=array(
				"order_id"=>$order_id,
				
				);
			
			$order_templates = dirname(__FILE__) ."/views/order-details.php";
			$html = $api->view->renderPartial('', $template_data, false, '', $order_templates);
			
			return $html ; 
		}
			
			
		return ;
	}
	
/**
	 * Return an instance of this class.
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

}

endif;

