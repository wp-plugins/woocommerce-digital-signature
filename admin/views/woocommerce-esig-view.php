<?php
if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

// To default a var, add it to an array
	
	// To default a var, add it to an array
	$vars = array(
		'messages', // will default $data['messages']
		'form_head', 
		'more_options', 
		'pdf_options', 
		'form_tail'
	);
	$this->default_vals($data, $vars);


	include($this->rootDir . DS . 'partials/_tab-nav.php'); 

	$esig_settings = new WP_E_Setting();
	
$esign_woo_agreement_setting=get_option('esign_woo_agreement_setting');
	
$esign_woo_sad_page=get_option('esign_woo_sad_page');

$agreement_disabled_agreement=(isset($esign_woo_agreement_setting) && $esign_woo_agreement_setting=='yes')?"checked":"";

?>

<div class="esign-misc-tab">
 <a class="misc_link" href="admin.php?page=esign-misc-general"><?php _e('General Option','esig-ulab') ?></a>  <?php echo $data['customizztion_more_links']; ?>
</div>



<h3><?php _e('E-signature Woocommerce Settings Page','esig-woocommerce') ?></h3>

<p class="esign-feedback-alert"><?php _e('<strong>Get Started:</strong> This WooCommerce settings page lets you specify a <a href="admin.php?page=esign-docs&document_status=stand_alone">Stand Alone Document</a> that all WooCommerce customers are required to sign in order to complete the checkout process.  Once the document has been signed they will be redirected to the final checkout page.<br /><br />You can also attach a individual documents to individual products on the <a href="edit.php?post_type=product">product page</a>.','esig-woocommerce'); ?></p>

<?php if (array_key_exists('message', $data)) { echo $data['message']; } ?>


 <div> <?php _e('This section lets you customize the WP E-Signature & Woocommerce Global Settings','esig-woocommerce') ?></div>

<form name="esig_woocommerce_form" class="esig_woocommerce_form" method="post" action="">	
<table class="form-table esig_woocommerce_form">
	<tbody>
        <tr>
			<th><label for="success_paragraph_text"><?php _e('Woocommerce Agreement','esig-woocommerce') ?></label></th>
				<td>
				<input type="checkbox" name="esign_woo_agreement_setting" value="yes" <?php echo $agreement_disabled_agreement; ?>> Enable </td>
		</tr>
    	<tr>
			<th><label for="esig_success_image" id="esig_success_image_label"><?php _e('Agreement Document','esig-woocommerce') ?></label></th>
			<td> 
			<select name="esign_woo_sad_page">
			<?php
			
			global $wpdb;
			$db_table =   $wpdb->prefix . 'esign_documents_stand_alone_docs';
			$stand_alone_pages = $wpdb->get_results("SELECT page_id, document_id FROM {$db_table}", OBJECT_K);
			
			foreach($stand_alone_pages as $sad_page)
			 {
				if($esign_woo_sad_page == $sad_page->page_id){ $selected="selected"; } else { $selected=""; }
				echo '<option value="'. $sad_page->page_id .'" '. $selected .' > '. get_the_title($sad_page->page_id ) .' </option>';	
			 }
			
			?>
			</select>
			</td>
		</tr>
		
	</tbody>
</table>

	<p> 
		<input type="submit" name="esig_woocommerce_submit"  class="button-appme button" value="<?php _e('Save Settings','esig-woocommerce') ?>" />
	</p>
</form>