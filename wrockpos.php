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
    // $cart = WC()->cart->get_cart();
    // echo '<pre>';
    // print_r($cart);
    // die;

    $current_page = $_REQUEST['currentPage'];   
    $args = array(
        'status'            => array( 'draft', 'pending', 'private', 'publish','inherit' ),
        'type'              => array_merge( array_keys( wc_get_product_types() ) ),
        'parent'            => null,
        'sku'               => '',
        'category'          => array(),
        'tag'               => array(),
        'limit'             => get_option( 'posts_per_page' ),  // -1 for unlimited        
        'offset'            => null,
        'page'              => $current_page,
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



class WRockpos_Cart extends WC_Cart {
    private $custom_cart_contents = array();

    public function add_to_cart($product_id, $quantity = 1, $variation_id = '', $variation = array(), $cart_item_data = array()) {
        $product_data = wc_get_product($product_id);

        // If the product is not valid, do not add it to the cart
        if (!$product_data || !$product_data->is_purchasable() || !$product_data->is_in_stock()) {
            return false;
        }

        // Generate a unique cart item key based on product ID, variation ID, and variation data
        $cart_item_key = parent::generate_cart_id($product_id, $variation_id, $variation, $cart_item_data);

        // Check if the product already exists in the custom cart
        $existing_item_key = $this->find_product_in_custom_cart($product_id, $variation_id, $variation, $cart_item_data);

        // If the product already exists in the custom cart, update the quantity
        if ($existing_item_key) {
            $this->custom_cart_contents[$existing_item_key]['quantity'] += $quantity;
        } else {
            // Otherwise, add the product as a new item to the custom cart
            $this->custom_cart_contents[$cart_item_key] = array(
                'product_id' => $product_id,
                'variation_id' => $variation_id,
                'variation' => $variation,
                'cart_item_data' => $cart_item_data,
                'quantity' => $quantity,
            );
        }

        // Calculate totals and set cart session data
        parent::calculate_totals();

        // Return the cart key of the added item
        return $cart_item_key;
    }

    // Helper function to find a product in the custom cart based on provided data
    private function find_product_in_custom_cart($product_id, $variation_id, $variation, $cart_item_data) {
        foreach ($this->custom_cart_contents as $cart_item_key => $cart_item) {
            if (
                $cart_item['product_id'] == $product_id &&
                $cart_item['variation_id'] == $variation_id &&
                $cart_item['variation'] == $variation &&
                $cart_item['cart_item_data'] == $cart_item_data
            ) {
                return $cart_item_key;
            }
        }
        return false;
    }

    public function get_custom_cart_contents() {
        // Implement your logic to retrieve custom cart contents
        $cart_items = array();
        foreach ($this->custom_cart_contents as $cart_item_key => $cart_item) {
            $product = wc_get_product($cart_item['product_id']);
            if ($product) {
                $cart_items[] = array(
                    'product_id' => $cart_item['product_id'],
                    'variation_id' => $cart_item['variation_id'],
                    'variation' => $cart_item['variation'],
                    'cart_item_data' => $cart_item['cart_item_data'],
                    'quantity' => $cart_item['quantity'],
                    'product_name' => $product->get_name(),
                    'product_price' => $product->get_price(),
                );
            }
        }
        return $cart_items;
    }
}

// Replace WooCommerce cart with your custom cart class
function wrockpos_woocommerce_cart_class($cart_class) {
    $cart_class = 'WRockpos_Cart';
    return $cart_class;
}
add_filter('woocommerce_cart_class', 'wrockpos_woocommerce_cart_class');
