<?php
/**
 * Plugin Name:       Rock POS
 * Description:       WooCommerce POS
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Version:           1.0.0
 * Author:            Rock POS
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rockpos
 */
require __DIR__ . '/vendor/autoload.php';
use Automattic\WooCommerce\Client;

add_action( 'admin_menu', 'rockpos_init_menu' );

/**
 * Init Admin Menu.
 *
 * @return void
 */
function rockpos_init_menu() {
    add_menu_page( __( 'Rock POS', 'rockpos'), __( 'Rock POS', 'rockpos'), 'manage_options', 'rockpos', 'rockpos_admin_page', 'dashicons-admin-post', '2.1' );
}

/**
 * Init Admin Page.
 *
 * @return void
 */
function rockpos_admin_page() {
    require_once plugin_dir_path( __FILE__ ) . 'templates/app.php';
}

add_action( 'admin_enqueue_scripts', 'rockpos_admin_enqueue_scripts' );

/**
 * Enqueue scripts and styles.
 *
 * @return void
 */
function rockpos_admin_enqueue_scripts() {
    wp_enqueue_style( 'rockpos-style', plugin_dir_url( __FILE__ ) . 'frontend/dist/assets/index.css' );   
    wp_enqueue_script( 'rockpos-script', plugin_dir_url(__FILE__) . 'frontend/dist/assets/index.js', array( 'wp-element' ), '1.0.0', true );    
}

add_action( 'wp_ajax_getProducts', 'getProducts_init' );
add_action( 'wp_ajax_nopriv_getProducts', 'getProducts_init' );
function getProducts_init() {
    $args = array(
        'status'            => array( 'draft', 'pending', 'private', 'publish' ),
        'type'              => array_merge( array_keys( wc_get_product_types() ) ),
        'parent'            => null,
        'sku'               => '',
        'category'          => array(),
        'tag'               => array(),
        'limit'             => get_option( 'posts_per_page' ),  // -1 for unlimited        
        'offset'            => null,
        'page'              => 1,
        'include'           => array(),
        'exclude'           => array(),
        'orderby'           => 'date',
        'order'             => 'DESC',
        'return'            => 'objects',
        'paginate'          => true,    
        'shipping_class'    => array(),
    );
    $result = wc_get_products($args);
    
//    echo '<pre>';
//    print_r($products->products);
//    die;
    
    $list = array();
    $i = 1;
    foreach($result->products as $product) {
        // Collect product variables
        $list[$i]['id']   = $product->get_id();
        $list[$i]['name'] = $product->get_name();
        $list[$i]['image'] = $product->get_image();
        $list[$i]['price'] = $product->get_price();
        //$list[$i]['attributes'] = $product->get_available_variations();
        $i++;
    }
    wp_send_json_success(array (
        'total_page' => $result->max_num_pages,
        'products' => $list
        ));
    die();
}