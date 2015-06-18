<?php
/**
 * Order
 *
 * @class    woocommerce_esignature
 * @version  2.2.0
 * @package  esig-woocommerce
 * @category Class
 * @author   approveme
 */

class esig_woocommerce_sad {
	
	
	 function __construct() {
		
		global $wpdb;
		$this->wpdb = $wpdb;
		
		$this->dbtable =   $this->wpdb->prefix . 'esign_documents_stand_alone_docs';
		    
	}
	
	 /**
	  *  get sad page array
	  * 
	  *  @since 1.0.0
	  */ 
	  public function esig_get_sad_pages()
		{
			if(! function_exists('WP_E_Sig'))
				return ;
				
		$esig = WP_E_Sig();
		$api = $esig->shortcode;
		
		$stand_alone_pages = $this->wpdb->get_results("SELECT page_id, document_id FROM {$this->dbtable}", OBJECT_K);
			
			$woo_array=array();
			
			$woo_array['pleaseslc']="Please Select Agreement Document"; 
			
			foreach($stand_alone_pages as $sad_page)
			{
				$document_status=$api->document->getStatus($sad_page->document_id); 
				
				if($document_status !='trash')
				{
					if('publish' === get_post_status($sad_page->page_id ))
					{
						$woo_array[$sad_page->page_id]=get_the_title($sad_page->page_id );
					}
				}
				
			}
			
			return $woo_array ; 
		}
		
		
	
	/**
	 *  Get sad page id by document id 
	 * 
	 *  @since 1.0.0
	 */ 
	
	public function get_sadpage_id_document_id($id)
	{
		return $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT page_id FROM " . $this->dbtable . " WHERE document_id=%s", $id
				)
			);
	}
	
	/**
	 *  Get sad document id by page id 
	 * 
	 *  @since 1.0.0
	 */ 
	
	public function get_sad_document_id($page_id)
	{
		return $this->wpdb->get_var(
			$this->wpdb->prepare(
				"SELECT document_id FROM " . $this->dbtable . " WHERE page_id=%s", $page_id
				)
			);
	}
	
	/**
	*  Check sad page is active and document is valid. 
	*/
	public function is_agreement_page_valid($page_id)
	{
		if(! function_exists('WP_E_Sig'))
				return ;
				
			$esig = WP_E_Sig();
			$api = $esig->shortcode;
			
		$document_id = $this->get_sad_document_id($page_id);
		if($api->document->document_exists($document_id) == 0)
		{
			return false ; 
		}
		$document_status=$api->document->getStatus($document_id); 
				
				if($document_status !='trash')
				{
					
					return true ;
				}
				else 
				{
					return false ;
				}
		return false ; 
	}
	
}