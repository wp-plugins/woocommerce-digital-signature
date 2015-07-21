<?php
/**
 *
 * @package ESIG_WOOCOMMERCE_Admin
 * @author  Abu Shoaib <team@approveme.me>
 */

if ( ! defined( 'ABSPATH' ) ) { 
	exit; // Exit if accessed directly
}

if (! class_exists('ESIG_WOOCOMMERCE_Admin')) :
class ESIG_WOOCOMMERCE_Admin {

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
		
		// usr action 
		add_action('admin_menu', array(&$this, 'esig_woocommerce_adminmenu'));
		
		add_filter('esig_misc_more_document_actions',array($this,'esig_misc_page_more_acitons'),10,1);
		
		add_action('woocommerce_before_checkout_form',array($this,'esig_before_checkout_form'),10);
		
		add_action('esig_document_complate',array($this,'esig_signature_after'),10,1);
		
		add_action( 'woocommerce_checkout_order_processed',array($this,'esig_new_woo_order'));
		
		add_action('woocommerce_cart_emptied',array($this,'esig_woo_cart_empty'));
		
		
		add_filter( 'woocommerce_get_settings_checkout', array($this,'esignature_all_settings'), 10, 1);
		
		add_action('admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
	
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
		    'woocommerce_page_wc-settings',
			
			);
		
		if (in_array($screen->id, $admin_screens)) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/esign-woocommerce.css', __FILE__ ), array());
		}

	}
	
	
	public function esignature_all_settings( $settings_esignature) 
	{
		/**
		* Check the current section is what we want
		**/
            
	    if(!function_exists('WP_E_Sig'))
	         return $settings_esignature;
	    
	           $img_link = ESIGN_ASSETS_DIR_URI . "/images/approveme-badge.png";
            
			// Add Title to the Settings
			$settings_esignature[] = array( 'name' => __( 'WooCommerce Digital Signature', 'esig-woocommerce' ), 'type' => 'title', 'desc' => __( '<div class="esign-woo-container"><div class="esign-woo-box notice-success"><h3><strong>Get Started:</strong></h3> <p>A Global Contract is pretty rad... it lets you set a “global contract” or "global agreement" for your entire e-commerce store. In short you can require ALL customers (regardless of the products they purchase) to sign a legal contract before completing their checkout.<br /><br />You can also attach a individual documents to individual products on the <a href="edit.php?post_type=product">product page</a>.</p><p>This section lets you customize the WP E-Signature & Woocommerce Global Settings<p><p><a href="https://www.approveme.me/profile" class="button-primary">Get My Approveme Downloads </a> <a href="index.php?page=esign-woocommerce-about" class="button">Need help getting started?</a></p></div><div class="esign-woo-box-right"><img src="'. $img_link .'"></div></div>', 'esig-woocommerce' ), 'id' => 'wpesignature' );
			
			// Add first checkbox option
			$settings_esignature[] = array(
				'name'     => __( 'Woocommerce Agreement', 'esig-woocommerce' ),
				'desc_tip' => __( 'This will automatically enable E-signature agreement', 'esig-woocommerce' ),
				'id'       => 'esign_woo_agreement_setting',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable', 'esig-woocommerce' ),
				);
			// adding sad dropdown. 
			
			$settings_esignature[] = array(
				'name'     => __( 'Agreement Document', 'esig-woocommerce' ),
				'desc_tip' => __( 'This WooCommerce settings page lets you specify a Stand Alone Document that all WooCommerce customers are required to sign in order to complete the checkout process. Once the document has been signed they will be redirected to the final checkout page.', 'esig-woocommerce' ),
				'id'       => 'esign_woo_sad_page',
				'type'     => 'select',
				'css'      => 'min-width:300px;',
				'options' =>$this->esig_sad->esig_get_sad_pages(),
				);
			$settings_esignature[] = array( 'type' => 'sectionend', 'id' => 'wpesignature' );

			return $settings_esignature;
		
		
	}
	/**
	* Create the section beneath the products tab
	**/
	public function esignature_add_section( $sections ) {
		
		$sections['wpesignature'] = __( 'WP E-signature', 'esig-woocommerce' );
		return $sections;
		
	}
	
	public function esig_woo_cart_empty()
	{
		
		if(!function_exists('WP_E_Sig'))
		return;
		
		$esig = WP_E_Sig();
		$api = $esig->shortcode;
		global $woocommerce;
		
		$woo_cart_id = $this->get_woocommerce_cart_id();
		
		delete_transient( 'esig-woo-signed-'.$woo_cart_id);
		
		
		$session_id= $this->get_woocommerce_cart_id();
		
		delete_transient('esig-woo-'.$session_id);
		 
	}
	
	public function delete_transient_order_product($order_id)
	{
		if(!function_exists('WP_E_Sig'))
				return;
			
		$esig = WP_E_Sig();
		$api = $esig->shortcode;
		global $woocommerce;
		$woo_cart_id = $this->get_woocommerce_cart_id();
		
		foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) 
		{
			$_product = $cart_item['data'];
			$product_id =$_product->id;
			$sad_page = get_post_meta($product_id, '_esig_woo_meta_sad_page', true );
			delete_transient( $woo_cart_id . $sad_page);
			
		}
		
		$esign_woo_sad_page=get_option('esign_woo_sad_page');
		 
		delete_transient( $woo_cart_id . $esign_woo_sad_page);
		// deleting all transient
		delete_transient('esig-woo-signed-'.$woo_cart_id);
		
	}
	
	
	public function esig_new_woo_order($order_id)
	{
		
		if(!function_exists('WP_E_Sig'))
				return;
		
			$esig = WP_E_Sig();
			
			$api = $esig->shortcode;
			
			global $woocommerce;
			
			$woo_cart_id = $this->get_woocommerce_cart_id();
		
			$doc_array= json_decode($api->setting->get_generic('esig-woo-'.$woo_cart_id));
			
			foreach($doc_array as $key => $document_id)
			{
					$allinvitation=$api->invite->getInviteBy('document_id',$document_id);
					$api->setting->set('esig-order-id'.$allinvitation->invitation_id,$order_id);
				
					// fire an action for each document loaded. 
					do_action('esig_signature_loaded', array('document_id' => $document_id,));
					// getting document 
					$doc= $api->document->getDocument($document_id);
					
					$attachments = apply_filters('esig_email_attachment',array('document' => $doc));
				
					$audit_hash = $api->auditReport($document_id, $doc, true);
				
					if(is_array($attachments) || empty($attachments)){
					
							$attachments=false ; 
					}
					
					
					$invite_hash = $api->invite->getInviteHash($allinvitation->invitation_id);
					$recipient_obj = $api->user->getUserByID($allinvitation->user_id);
				
					$api->notify_owner($doc, $recipient_obj, $audit_hash,$attachments); // Notify admin
				
					$post = array('invite_hash'=>$invite_hash, 'checksum'=>$doc->document_checksum);
				
					$api->notify_signer($doc, $recipient_obj, $post, $audit_hash,$attachments); // Notify signer
			}
			// deleting neccessary transient
			
			$this->delete_transient_order_product($order_id);
		
			// removing previous settings from and saving new settings with order id
			$api->setting->delete('esig-woo-'.$woo_cart_id);
			// new settings with order id.
			$api->setting->set('esig-woo-order-docs-'.$order_id,json_encode($doc_array));
			// do action after sending email 
			do_action('esig_email_sent',array('document'=>$doc));
					
			
	}
	
	
	
	
	public function esig_signature_after($args)
	{
		if(!function_exists('WP_E_Sig'))
				return;
		
			
			$esig = WP_E_Sig();
			
			$api = $esig->shortcode;
			
			global $woocommerce;
			
			$document_id = $args['invitation']->document_id;
			$sad_doc_id = $args['sad_doc_id'];
			
			$page_id=$this->esig_sad->get_sadpage_id_document_id($sad_doc_id);
			
			// if this is not woo page 
			$not_woo_agreement=true ; 
			
			$woo_cart_id = $this->get_woocommerce_cart_id();
			
			if ( sizeof( $woocommerce->cart->cart_contents) == 0 ) 
			{ 
					return ;
			}
			
			if ( sizeof( $woocommerce->cart->cart_contents) > 0 ) 
			{ 
			    foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item )
			    {
			        	
			        $_product = $cart_item['data'];
			        	
			        $product_id =$_product->id;
			        $product_agreement = get_post_meta($product_id, '_esig_woo_meta_product_agreement', true );
			  	
			        if(!empty($product_agreement))
			        {
			                $sad_page = get_post_meta($product_id, '_esig_woo_meta_sad_page', true );
			                if($page_id == $sad_page)
			                {
			                    
    			                set_transient( $woo_cart_id . $page_id ,'signed', 3600);
    			                $this->esig_woo_delete_add_list($document_id);
    			                $api->setting->set_array('esig-woo-'.$woo_cart_id,$document_id);   
    			                $not_woo_agreement=false ; 
			                }
			        }
			       
			        
			    }
							
			}
			
				// checking global woocommerce settings. 
				$esig_woo_agreement =get_option('esign_woo_agreement_setting');
				
			    if (!empty($esig_woo_agreement))
			        {
			            $esign_woo_sad_page=get_option('esign_woo_sad_page');
			            
			            if($page_id == $esign_woo_sad_page)
			            {
			            	
			                set_transient( $woo_cart_id . $page_id ,'signed', 3600);
			                $this->esig_woo_delete_add_list($document_id);
			                $api->setting->set_array('esig-woo-'.$woo_cart_id,$document_id);				
			                $not_woo_agreement=false ; 
			            }
			                 
			     }
			     else
			     {
				 	return ; 
				 }
		
		if($not_woo_agreement)
		{
			return ; 
		}
		$this->esig_before_checkout_form();
		
		 
		
			wp_redirect($woocommerce->cart->get_checkout_url());
			exit;
		
	}
	
	public function esig_woo_delete_add_list($document_id)
	{
		
		if(!function_exists('WP_E_Sig'))
				return;
		
		
			$esig = WP_E_Sig();
			$api = $esig->shortcode;
			
		$woo_delete = $api->setting->get_generic('esign_delete_woo_cart');
		if(!$woo_delete)
		{
			$woo_delete=array();
		}
		else
		{
			$woo_delete=json_decode($woo_delete);
		}
		
		$woo_delete[]=$document_id;
		
		$api->setting->set('esign_delete_woo_cart',json_encode($woo_delete));
		
	}
	
	public function esig_misc_page_more_acitons($misc_more_actions){
		
		
		$class=(isset($_GET['page']) && $_GET['page']=='esign-woocommerce')?'misc_current':'';
		$misc_more_actions .=' | <a class="misc_link '. $class .'" href="admin.php?page=wc-settings&tab=checkout">'.__('WooCommerce','esig-woocommerce').'</a>';
		
		return $misc_more_actions ; 

	}
	
	/**
	 * This is method esig_usr_adminmenu
	 *   Create a admin menu for esinature roles . 
	 * @return mixed This is the return value description
	 */    
	public function esig_woocommerce_adminmenu()
	{
		add_submenu_page(null, __( 'About', 'esig' ), __( 'About', 'esig' ), 'read', 'esign-woocommerce-about', array(&$this, 'woo_about_page'));
		
		if(!function_exists('WP_E_Sig'))
				return;
		
		$esigrole = new WP_E_Esigrole();
		if($esigrole->esig_current_user_can('have_licenses'))
		{
			add_submenu_page(null, __('Esig Woocommerce Settings Page','esig-woocommerce'), __('Esig Woocommerce Settings Page','esig-woocommerce'), 'read','esign-woocommerce', array(&$this, 'esign_woocommerce_view'));
			add_submenu_page(null, __('Esig Woocommerce Core not installed','esig-woocommerce'), __('Esig Woocommerce Core not installed','esig-woocommerce'), 'read','esign-not-core', array(&$this, 'esign_not_core_view'));
		}
		
	}
	
	
	public function woo_about_page()
	{
		
		include_once(dirname(__FILE__) ."/views/woocommerce-esign-about.php");
		
	}
	
	
	public function esign_not_core_view(){
		
		$template_data=array(
			"ESIGN_ASSETS_DIR_URI"=>ESIGN_ASSETS_DIR_URI,
			
			);

		$branding_template = dirname(__FILE__) ."/views/esig-not-core-view.php";
		$api->view->renderPartial('', $template_data, true, '', $branding_template);
		
	}
	/***
	  * Adding success page content view 
	  * @Since 1.1.3
	  */
	public function esign_woocommerce_view(){
		
		if(!function_exists('WP_E_Sig'))
				return;
			
		
		$esig = WP_E_Sig();
		$api = $esig->shortcode;
		//calling esignature setings class to save data in settings table
		$esig_general = new WP_E_General();
		$esig_settings = new WP_E_Setting();
		
		// loading whiskers with constructing initials 
		
		$wp_user_id = get_current_user_id();
		$misc_more_actions = apply_filters('esig_misc_more_document_actions','');
		
		if(count($_POST) > 0  && isset($_POST['esig_woocommerce_submit']) && $_POST['esig_woocommerce_submit']=='Save Settings')
		{
			$esign_woo_agreement_setting= isset($_POST['esign_woo_agreement_setting'])?$_POST['esign_woo_agreement_setting']:'';
			$esign_woo_sad_page=isset($_POST['esign_woo_sad_page'])? $_POST['esign_woo_sad_page']:'';
			// going to save settings 
			if(get_option('esign_woo_agreement_setting'))
			{
				update_option('esign_woo_agreement_setting',$esign_woo_agreement_setting);
			}
			else 
			{
				add_option('esign_woo_agreement_setting',$esign_woo_agreement_setting);
			}
			// sad page adding. 
			if(get_option('esign_woo_sad_page'))
			{
				update_option('esign_woo_sad_page',$esign_woo_sad_page);
			}
			else 
			{
				add_option('esign_woo_sad_page',$esign_woo_sad_page);
			}
			
		}

			
		$template_data=array(
			"ESIGN_ASSETS_DIR_URI"=>ESIGN_ASSETS_DIR_URI,
			"Licenses"=> $esig_general->checking_extension(),
			"misc_tab_class"=>'nav-tab-active',
			"customizztion_more_links"=> $misc_more_actions,
			
			);

		$branding_template = dirname(__FILE__) ."/views/woocommerce-esig-view.php";
		$api->view->renderPartial('', $template_data, true, '', $branding_template);
		
	}
	
	public function esig_before_checkout_form()
	{
		
		if(!function_exists('WP_E_Sig'))
				return;
		
		$esig = WP_E_Sig();
		$api = $esig->shortcode;
		global $woocommerce;
		
		$woo_cart_id = $this->get_woocommerce_cart_id();
		
		$esign_woo_sad_page=$this->is_signature_needs($woo_cart_id);
		
		$signed=get_transient( 'esig-woo-signed-'.$woo_cart_id);
		
		if($signed=='all_signed')
		{
			return ;
		}

		//exit;

		if($esign_woo_sad_page)
		{
			$permalink = get_permalink($esign_woo_sad_page);
			wp_redirect($permalink);
			exit;
		}
			
	}
	
    /***
     *  Return bolean 
     * 
     * */
	
	public function is_signature_needs($woo_cart_id)
	{
		if(!function_exists('WP_E_Sig'))
				return;
		
		$esig = WP_E_Sig();
		$api = $esig->shortcode;
		global $woocommerce;
		
		$sad_page=false;
		
		$woo_cart_id = $this->get_woocommerce_cart_id();
		
		foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) 
		{
			
			$_product = $cart_item['data'];
			
			$product_id =$_product->id;
			
			$product_agreement = get_post_meta($product_id, '_esig_woo_meta_product_agreement', true );
			
			if($product_agreement)
			{
				$sad_page = get_post_meta($product_id, '_esig_woo_meta_sad_page', true );
				if(!$this->esig_sad->is_agreement_page_valid($sad_page))
				{
					$sad_page=false;	
					break;
				}
				$signed=get_transient($woo_cart_id . $sad_page);
				
				if($signed !='signed')
				{
					break;
				}
				else
				{
					$sad_page=false;	
				}
			}
		} 
		
		// if sad page is true then return 
		if($sad_page)
		{
			return $sad_page;
		}
		
		$esig_woo_agreement =get_option('esign_woo_agreement_setting');
		
		
		if($esig_woo_agreement == "yes")
		{
			//exit;
			$esign_woo_sad_page=get_option('esign_woo_sad_page');
			
			if(!$this->esig_sad->is_agreement_page_valid($esign_woo_sad_page))
			{
				return false ; 
			}
			
			$signed=get_transient($woo_cart_id . $esign_woo_sad_page);
			
			if($signed !='signed')
			{
				
				return $esign_woo_sad_page;
			}
			
				
		}
		
		
		set_transient('esig-woo-signed-'.$woo_cart_id,'all_signed',3600);
	
		return false;
	}
	
	public function get_woocommerce_cart_id()
	{
		if (!class_exists('Woocommerce')) 
		{
			return ; 
		}
		global $woocommerce;
		
		
		if(!isset($_COOKIE['esig-woo-session']))
		{
			$woo_session=$woocommerce->session->get_session_cookie();
			$session_id=$woo_session[3];
			wc_setcookie('esig-woo-session', $session_id, time()+3600 ) ;
		}
		else
		{
			$session_id=$_COOKIE['esig-woo-session'];
		}
		
		if (!get_transient('esig-woo-'.$session_id)) 
		{
			set_transient('esig-woo-'.$session_id,$session_id,time()+3600);
			return $session_id; 
		}
		else
		{
			return get_transient('esig-woo-'.$session_id); 
		}	
		
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

