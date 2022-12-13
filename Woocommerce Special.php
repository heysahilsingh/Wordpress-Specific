/*============== SHOW OUT OF STOCK PRODUCTS IN LAST START ==============*/
add_action( 'pre_get_posts', function ( $q ) {
    if (   !is_admin()                 // Target only front end 
         && $q->is_main_query()        // Only target the main query
         && $q->is_post_type_archive() // Change to suite your needs
    ) {
        $q->set( 'meta_key', '_stock_status' );
        $q->set( 'orderby',  'meta_value'    );
        $q->set( 'order',    'ASC'           );
	    }
}, PHP_INT_MAX );
/*============== SHOW OUT OF STOCK PRODUCTS IN LAST END ==============*/


/*============== SEARCH PRODUCT BY SKU START ==============*/
 function search_by_sku( $search, &$query_vars ) {
    global $wpdb;
    if(isset($query_vars->query['s']) && !empty($query_vars->query['s'])){
        $args = array(
            'posts_per_page'  => -1,
            'post_type'       => 'product',
            'meta_query' => array(
                array(
                    'key' => '_sku',
                    'value' => $query_vars->query['s'],
                    'compare' => 'LIKE'
                )
            )
        );
        $posts = get_posts($args);
        if(empty($posts)) return $search;
        $get_post_ids = array();
        foreach($posts as $post){
            $get_post_ids[] = $post->ID;
        }
        if(sizeof( $get_post_ids ) > 0 ) {
                $search = str_replace( 'AND (((', "AND ((({$wpdb->posts}.ID IN (" . implode( ',', $get_post_ids ) . ")) OR (", $search);
        }
    }
    return $search;
}
    add_filter( 'posts_search', 'search_by_sku', 999, 2 ); 
/*============== SEARCH PRODUCT BY SKU END ==============*/


/*============== AJAX CART COUNT START ==============*/
//Shortcode For Cart Count "[sa_cart_number]"
add_shortcode('sa_cart_number', 'sa_cart_number');

//Create Shortcode for WooCommerce Cart Menu Item
function sa_cart_number()
{
    ob_start();
    $cart_count = WC()
        ->cart->cart_contents_count; // Set variable for cart item count
    $cart_url = wc_get_cart_url(); // Set Cart URL
?>
<a class="menu-item cart-contents" href="<?php echo $cart_url; ?>" title="My Basket">
   <?php if ($cart_count > 0) { ?>
            <span class="cart-contents-count"><?php echo $cart_count; ?></span>
   <?php }
         else { ?>
            <span class="cart-contents-count">0</span>
   <?php } ?>
</a>
<?php return ob_get_clean(); }

//Add a filter to get the cart count
add_filter('woocommerce_add_to_cart_fragments', 'sa_cart_number_count');

//Add AJAX Shortcode when cart contents update
function sa_cart_number_count($fragments)
{
    ob_start();
    $cart_count = WC()
        ->cart->cart_contents_count;
    $cart_url = wc_get_cart_url();

?>
<a class="cart-contents menu-item" href="<?php echo $cart_url; ?>" title="<?php _e('View your shopping cart'); ?>">
   <?php if ($cart_count > 0) { ?>
            <span class="cart-contents-count"><?php echo $cart_count; ?></span>
        <?php }
         else{ ?>
            <span class="cart-contents-count">0</span>
        <?php } ?>
</a>
    <?php
    $fragments['a.cart-contents'] = ob_get_clean();
    return $fragments;}
/*============== AJAX CART COUNT END ==============*/


/*============== CHANGE CART BUTTON TEXT IF PRODUCT ALREADY IN CART START ==============*/
function change_add_to_cart_text_if_product_already_in_cart( $add_to_cart_text, $product ) {    
    if ( WC()->cart ) {
        $cart = WC()->cart; // Get cart
        if ( ! $cart->is_empty() ) {
            foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
                $_product_id = $cart_item['product_id'];
                if ( $product->get_id() == $_product_id ) {
                    $add_to_cart_text = '('.$cart_item['quantity'].')'.' AAdded In Cart';
                    break;
                }
            }
        }
    }
    return $add_to_cart_text;
}
add_filter( 'woocommerce_product_add_to_cart_text', 'change_add_to_cart_text_if_product_already_in_cart', 10, 2 );
add_filter( 'woocommerce_product_single_add_to_cart_text', 'change_add_to_cart_text_if_product_already_in_cart', 10, 2 );
/*============== CHANGE CART BUTTON TEXT IF PRODUCT ALREADY IN CART END ==============*/


/*============== BUY NOW BUTTON START ==============*/
function sbw_wc_add_buy_now_button_single()
{   global $product;
    printf( '<button id="sbw_wc-adding-button" type="submit" name="sbw-wc-buy-now" value="%d" class="single_add_to_cart_button buy_now_button button alt">%s</button>', $product->get_ID(), esc_html__( 'Buy Now', 'sbw-wc' ) );
}
add_action( 'woocommerce_after_add_to_cart_button', 'sbw_wc_add_buy_now_button_single' );

// Button for click on Buy Now
function sbw_wc_handle_buy_now()
{
    if ( !isset( $_REQUEST['sbw-wc-buy-now'] ) )
    {
        return false;
    }
    WC()->cart->empty_cart();
    $product_id = absint( $_REQUEST['sbw-wc-buy-now'] );
    $quantity = absint( $_REQUEST['quantity'] );
    if ( isset( $_REQUEST['variation_id'] ) ) {
        $variation_id = absint( $_REQUEST['variation_id'] );
        WC()->cart->add_to_cart( $product_id, 1, $variation_id );
    }else{
        WC()->cart->add_to_cart( $product_id, $quantity );
    }
    wp_safe_redirect( wc_get_checkout_url() );
    exit;
}
add_action( 'wp_loaded', 'sbw_wc_handle_buy_now' );
/*============== BUY NOW BUTTON END ==============*/


/*============== ADD WISHLIST SHORTCODE IN MY ACCOUNT PAGE START ==============*/
add_action( 'woocommerce_account_dashboard', 'custom_account_dashboard_content' );
function custom_account_dashboard_content(){
    echo do_shortcode('[wishsuite_table]');
}
/*============== ADD WISHLIST SHORTCODE IN MY ACCOUNT PAGE END ==============*/


/*============== REORDER TABS @ AC PAGE START ==============*/
function reorder_account_menu( $items ) {
    return array(
		    'dashboard'       => __( 'My Wishlist', 'woocommerce' ),
	        'orders'             => __( 'My Orders', 'woocommerce' ),
		    'edit-account'       => __( 'Edit Account Details', 'woocommerce' ),
	        'edit-address'       => __( 'Edit Address', 'woocommerce' ),
	        'customer-logout'    => __( 'Logout', 'woocommerce' ),	    
	);}

add_filter ( 'woocommerce_account_menu_items', 'reorder_account_menu' );
/*============== REORDER TABS @ AC PAGE END ==============*/


/*============== MAKE VARIATION DROPTOWN TO RADIO START ==============*/
add_action( 'woocommerce_variable_add_to_cart', function() {
    add_action( 'wp_print_footer_scripts', function() {
        ?>
        <script type="text/javascript">
        // DOM Loaded
        document.addEventListener( 'DOMContentLoaded', function() {
            // Get Variation Pricing Data
            var variations_form = document.querySelector( 'form.variations_form' );
            var data = variations_form.getAttribute( 'data-product_variations' );
            data = JSON.parse( data );
            // Loop Drop Downs
            document.querySelectorAll( 'table.variations select' )
                .forEach( function( select ) {
                // Loop Drop Down Options
                select.querySelectorAll( 'option' )
                    .forEach( function( option ) {
                    // Skip Empty
                    if( ! option.value ) {
                        return;
                    }
                    // Get Pricing For This Option
                    var pricing = '';
                    data.forEach( function( row ) {
                        if( row.attributes[select.name] == option.value ) {
                            pricing = row.price_html;
                        }
                    } );
                    // Create Radio
                    var radio = document.createElement( 'input' );
                        radio.type = 'radio';
                        radio.name = select.name;
                        radio.value = option.value;
                        radio.checked = option.selected;
                    var label = document.createElement( 'label' );
                        label.appendChild( radio );
                        label.appendChild( document.createTextNode( ' ' + option.text + ' ' ) );
                    var span = document.createElement( 'span' );
                        span.innerHTML = pricing;
                        label.appendChild( span );
                    var div = document.createElement( 'div' );
                        div.appendChild( label );
                    // Insert Radio
                    select.closest( 'td' ).appendChild( div );
                    // Handle Clicking
                    radio.addEventListener( 'click', function( event ) {
                        select.value = radio.value;
                        jQuery( select ).trigger( 'change' );
                    } );
                } ); // End Drop Down Options Loop
                // Hide Drop Down
                select.style.display = 'none';
            } ); // End Drop Downs Loop
        } ); // End Document Loaded
        </script>
        <?php
    } );
} );
/*============== MAKE VARIATION DROPTOWN TO RADIO END ==============*/


/*============== SHOW % DISCOUNTED PRODUCT PRICE START ==============*/
add_filter( 'woocommerce_get_price_html', 'woo_alter_price_display', 9999, 2 );
function woo_alter_price_display( $price_html, $product ) {
  // ONLY ON FRONTEND
  if ( is_admin() ) return $price_html;

  // ONLY IF PRICE NOT NULL
  if ( '' === $product->get_price() ) return $price_html;
      $price = $product->get_price(); //GET product price
      $orig_price = ₹ . '' . number_format( wc_get_price_to_display( $product ) ); //Original Price
	  $orig_price_html = "<span>$orig_price</span>";
	  $discounted_price = ₹ . '' .  number_format( round( $price * 0.85 ) ); //Define discount here
      $discounted_price_html = "<span>$discounted_price</span>"; //Discounted Price
	  $saved_amount = ₹ . '' . number_format( wc_get_price_to_display( $product ) - $price * 0.85 );
	  $saved_percentage = "<span>(15% off)</span>";
	  $saved_amount_html = "<span>(You Just Saved $saved_amount)</span>";
	
      return $orig_price_html . '' . $discounted_price_html . '' . $saved_percentage . '' . $saved_amount_html;
}
/*============== SHOW % DISCOUNTED PRODUCT PRICE END ==============*/


/*============== ADD CUSTOM FIELDS IN REGISTRATION FORM START ==============*/
function wooc_extra_register_fields() {?>
       <p class="form-row form-row-first">
       <label for="reg_billing_first_name"><?php _e( 'First name', 'woocommerce' ); ?><span class="required">*</span></label>
       <input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" value="<?php if ( ! empty( $_POST['billing_first_name'] ) ) esc_attr_e( $_POST['billing_first_name'] ); ?>" />
       </p>

       <p class="form-row form-row-last">
       <label for="reg_billing_last_name"><?php _e( 'Last name', 'woocommerce' ); ?><span class="required">*</span></label>
       <input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" value="<?php if ( ! empty( $_POST['billing_last_name'] ) ) esc_attr_e( $_POST['billing_last_name'] ); ?>" />
       </p>

       <p class="form-row form-row-first">
       <label for="reg_billing_phone"><?php _e( 'Phone', 'woocommerce' ); ?><span class="required">*</span></label>
       <input input type="tel" class="input-text" name="billing_phone" id="reg_billing_phone" value="<?php esc_attr_e( $_POST['billing_phone'] ); ?>" pattern="[6-9]{1}[0-9]{9}" title="Only Indian number is acceptable."/>
       </p>
       
       <p class="form-row form-row-last">
       <label for="reg_billing_state"><?php _e( 'City or State', 'woocommerce' ); ?><span class="required">*</span></label>
       <input type="text" class="input-text" name="billing_state" id="reg_billing_state" value="<?php if ( ! empty( $_POST['billing_state'] ) ) esc_attr_e( $_POST['billing_state'] ); ?>" />
       </p> 

       <div class="clear"></div>
       <?php
 }
 add_action( 'woocommerce_register_form_start', 'wooc_extra_register_fields' );


 /**
* register fields Validating.
*/
function wooc_validate_extra_register_fields( $username, $email, $validation_errors ) {
    if ( isset( $_POST['billing_first_name'] ) && empty( $_POST['billing_first_name'] ) ) {
           $validation_errors->add( 'billing_first_name_error', __( 'Please enter your First name', 'woocommerce' ) );
    }
    if ( isset( $_POST['billing_last_name'] ) && empty( $_POST['billing_last_name'] ) ) {
           $validation_errors->add( 'billing_last_name_error', __( 'Please enter your Last name.', 'woocommerce' ) );
    }
    if ( isset( $_POST['billing_phone'] ) && empty( $_POST['billing_phone'] ) ) {
        $validation_errors->add( 'billing_phone_error', __( 'Please enter your Phone Number.', 'woocommerce' ) );
    }
    if ( isset( $_POST['billing_state'] ) && empty( $_POST['billing_state'] ) ) {
        $validation_errors->add( 'billing_state_error', __( 'Please enter your State or City.', 'woocommerce' ) );
    }

       return $validation_errors;
}
add_action( 'woocommerce_register_post', 'wooc_validate_extra_register_fields', 10, 3 );

/**
* Below code save extra fields.
*/
function wooc_save_extra_register_fields( $customer_id ) {
    if ( isset( $_POST['billing_phone'] ) ) {
                 // Phone input filed which is used in WooCommerce
                 update_user_meta( $customer_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
          }
      if ( isset( $_POST['billing_first_name'] ) ) {
             //First name field which is by default
             update_user_meta( $customer_id, 'first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
             // First name field which is used in WooCommerce
             update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
      }
      if ( isset( $_POST['billing_last_name'] ) ) {
             // Last name field which is by default
             update_user_meta( $customer_id, 'last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
             // Last name field which is used in WooCommerce
             update_user_meta( $customer_id, 'billing_last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
      }
}
add_action( 'woocommerce_created_customer', 'wooc_save_extra_register_fields' );
/*============== ADD CUSTOM FIELDS IN REGISTRATION FORM END ==============*/
