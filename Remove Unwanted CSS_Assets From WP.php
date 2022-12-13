/*============== REMOVE UNWANTED WP ASSETS START ==============*/
// remove emoji support
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

// Remove rss feed links
remove_action( 'wp_head', 'feed_links_extra', 3 );
remove_action( 'wp_head', 'feed_links', 2 );

// remove wp-embed
add_action( 'wp_footer', function(){
    wp_dequeue_script( 'wp-embed' );
});

add_action( 'wp_enqueue_scripts', function(){
    // // remove block library css
    wp_dequeue_style( 'wp-block-library' );
    // // remove comment reply JS
    wp_dequeue_script( 'comment-reply' );
} );

// Remove Deafult WP CSS
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );

//Remove CSS assets from Gutenberg Blocks & WooCommerce Blocks
add_action( 'wp_enqueue_scripts', function() {
    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'wp-block-library-theme' );
    wp_dequeue_style( 'wc-blocks-style' );
}, 100 );

//Remove Elementor Icon's CSS
add_action( 'wp_enqueue_scripts', 'disable_eicons', 11 );
function disable_eicons() {
	wp_dequeue_style( 'elementor-icons' );
	wp_deregister_style( 'elementor-icons' );}

//Elementor - disable Google Fonts
add_filter( 'elementor/frontend/print_google_fonts', 'return_false' );
add_filter( 'elementor/fonts/additional_fonts', 'return_false' );
/*============== REMOVE UNWANTED WP ASSETS END ==============*/