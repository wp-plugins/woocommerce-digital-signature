<?php
// Silence is golden
if ( ! defined( 'ABSPATH' ) ) { 
	exit; // Exit if accessed directly
}
// Add an nonce field so we can check for it later.
wp_nonce_field('esig_woo_product_nonce', 'esig_woo_product_box_nonce');


$product_agreement = get_post_meta($data['ID'], '_esig_woo_meta_product_agreement', true );

$esign_woo_sad_page = get_post_meta($data['ID'], '_esig_woo_meta_sad_page', true );

 if($product_agreement)
{
   $checked="checked";	
}
else
{
	$checked="";
}

?>
 <div><input type="checkbox" name="esig_product_agreement" value="1" <?php echo $checked; ?>><?php _e('Require purchasers to sign a contract','esig-woocommerce'); ?></div>

 <div><h4><?php _e('What agreement needs to be signed?','esig-woocommerce'); ?></h4></div>

<div><select name="esign_woo_sad_page">
			<?php
			
			$esig_sad = new esig_woocommerce_sad();
			$stand_alone_pages = $esig_sad->esig_get_sad_pages();
			
			foreach($stand_alone_pages as $sad_key => $sad_page)
			{
			    
				if($esign_woo_sad_page == $sad_key){ $selected="selected"; } else { $selected=""; }
				echo '<option value="'. $sad_key .'" '. $selected .' > '. $sad_page .' </option>';	
			}
			
			?></select></div>

<div>&nbsp;</div>			
 <div><a href="edit.php?post_type=esign&page=esign-add-document&esig_type=sad"><?php _e('Create a new document','esig-woocommerce'); ?></a><br><a href="index.php?page=esign-woocommerce-about">Need help?</a></div>

