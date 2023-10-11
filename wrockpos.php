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

add_action( 'wp_ajax_getProducts', 'getProducts_init');
add_action( 'wp_ajax_nopriv_getProducts', 'getProducts_init' );

function getProducts_init() {
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
    // echo '<pre>';
    // print_r($result);
    // die;
    $list = array();
    $i = 1;

    foreach($result->products as $product) {
        $type = $product->get_type();
        $list[$i]['id']   = $product->get_id();
        $list[$i]['name'] = $product->get_name();
        $list[$i]['image'] = $product->get_image();
        $list[$i]['price'] = $product->get_price();
        $list[$i]['type'] = $type;
        if ($type == 'variable') {
            $list[$i]['variations'] = $product->get_available_variations();
            $list[$i]['attributes'] = $product->get_variation_attributes();
        }
        $i++;
    }
    // echo '<pre>';
    // print_r($list);
    // die;
    wp_send_json_success(array (
        'total_page' => $result->max_num_pages,
        'products' => $list
        ));
    die();
}



class WRockpos_Cart extends WC_Cart {
    private $custom_cart_contents = array();

    public function add_to_cart($product_id = 0, $quantity = 1, $variation_id = 0, $variation = array(), $cart_item_data = array()) {
        $product_id   = absint( $product_id );
        $variation_id = absint( $variation_id );
        
        // Ensure we don't add a variation to the cart directly by variation ID.
        if ( 'product_variation' === get_post_type( $product_id ) ) {
            $variation_id = $product_id;
            $product_id   = wp_get_post_parent_id( $variation_id );
        }

        $product_data = wc_get_product( $variation_id ? $variation_id : $product_id );
        $quantity     = apply_filters( 'woocommerce_add_to_cart_quantity', $quantity, $product_id );
        
        if ( $quantity <= 0 || ! $product_data || 'trash' === $product_data->get_status() ) {
            return false;
        }

        if ( $product_data->is_type( 'variation' ) ) {
            $missing_attributes = array();
            $parent_data        = wc_get_product( $product_data->get_parent_id() );

            $variation_attributes = $product_data->get_variation_attributes();
            // Filter out 'any' variations, which are empty, as they need to be explicitly specified while adding to cart.
            $variation_attributes = array_filter( $variation_attributes );

            // Gather posted attributes.
            $posted_attributes = array();
            foreach ( $parent_data->get_attributes() as $attribute ) {
                if ( ! $attribute['is_variation'] ) {
                    continue;
                }
                $attribute_key = 'attribute_' . sanitize_title( $attribute['name'] );

                if ( isset( $variation[ $attribute_key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    if ( $attribute['is_taxonomy'] ) {
                        // Don't use wc_clean as it destroys sanitized characters.
                        $value = sanitize_title( wp_unslash( $variation[ $attribute_key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    } else {
                        $value = html_entity_decode( wc_clean( wp_unslash( $variation[ $attribute_key ] ) ), ENT_QUOTES, get_bloginfo( 'charset' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    }

                    // Don't include if it's empty.
                    if ( ! empty( $value ) || '0' === $value ) {
                        $posted_attributes[ $attribute_key ] = $value;
                    }
                }
            }

            // Merge variation attributes and posted attributes.
            $posted_and_variation_attributes = array_merge( $variation_attributes, $posted_attributes );

            // If no variation ID is set, attempt to get a variation ID from posted attributes.
            if ( empty( $variation_id ) ) {
                $data_store   = WC_Data_Store::load( 'product' );
                $variation_id = $data_store->find_matching_product_variation( $parent_data, $posted_attributes );
            }

            // Do we have a variation ID?
            if ( empty( $variation_id ) ) {
                throw new Exception( __( 'Please choose product options&hellip;', 'woocommerce' ) );
            }

            // Check the data we have is valid.
            $variation_data = wc_get_product_variation_attributes( $variation_id );
            $attributes     = array();

            foreach ( $parent_data->get_attributes() as $attribute ) {
                if ( ! $attribute['is_variation'] ) {
                    continue;
                }

                // Get valid value from variation data.
                $attribute_key = 'attribute_' . sanitize_title( $attribute['name'] );
                $valid_value   = isset( $variation_data[ $attribute_key ] ) ? $variation_data[ $attribute_key ] : '';

                /**
                 * If the attribute value was posted, check if it's valid.
                 *
                 * If no attribute was posted, only error if the variation has an 'any' attribute which requires a value.
                 */
                if ( isset( $posted_and_variation_attributes[ $attribute_key ] ) ) {
                    $value = $posted_and_variation_attributes[ $attribute_key ];

                    // Allow if valid or show error.
                    if ( $valid_value === $value ) {
                        $attributes[ $attribute_key ] = $value;
                    } elseif ( '' === $valid_value && in_array( $value, $attribute->get_slugs(), true ) ) {
                        // If valid values are empty, this is an 'any' variation so get all possible values.
                        $attributes[ $attribute_key ] = $value;
                    } else {
                        /* translators: %s: Attribute name. */
                        throw new Exception( sprintf( __( 'Invalid value posted for %s', 'woocommerce' ), wc_attribute_label( $attribute['name'] ) ) );
                    }
                } elseif ( '' === $valid_value ) {
                    $missing_attributes[] = wc_attribute_label( $attribute['name'] );
                }

                $variation = $attributes;
            }
            if ( ! empty( $missing_attributes ) ) {
                /* translators: %s: Attribute name. */
                throw new Exception( sprintf( _n( '%s is a required field', '%s are required fields', count( $missing_attributes ), 'woocommerce' ), wc_format_list_of_items( $missing_attributes ) ) );
            }
        }

        // Validate variation ID.
        if (
            0 < $variation_id && // Only check if there's any variation_id.
            (
                ! $product_data->is_type( 'variation' ) || // Check if isn't a variation, it suppose to be a variation at this point.
                $product_data->get_parent_id() !== $product_id // Check if belongs to the selected variable product.
            )
        ) {
            $product = wc_get_product( $product_id );

            /* translators: 1: product link, 2: product name */
            throw new Exception( sprintf( __( 'The selected product isn\'t a variation of %2$s, please choose product options by visiting <a href="%1$s" title="%2$s">%2$s</a>.', 'woocommerce' ), esc_url( $product->get_permalink() ), esc_html( $product->get_name() ) ) );
        }

        // Load cart item data - may be added by other plugins.
        $cart_item_data = (array) apply_filters( 'woocommerce_add_cart_item_data', $cart_item_data, $product_id, $variation_id, $quantity );

        // Generate a ID based on product ID, variation ID, variation data, and other cart item data.
        $cart_id = $this->generate_cart_id( $product_id, $variation_id, $variation, $cart_item_data );

        // Find the cart item key in the existing cart.
        $cart_item_key = $this->find_product_in_custom_cart( $cart_id);
        
        // Force quantity to 1 if sold individually and check for existing item in cart.
        if ( $product_data->is_sold_individually() ) {
            $quantity      = apply_filters( 'woocommerce_add_to_cart_sold_individually_quantity', 1, $quantity, $product_id, $variation_id, $cart_item_data );
            $found_in_cart = apply_filters( 'woocommerce_add_to_cart_sold_individually_found_in_cart', $cart_item_key && $this->custom_cart_contents[ $cart_item_key ]['quantity'] > 0, $product_id, $variation_id, $cart_item_data, $cart_id );

            if ( $found_in_cart ) {
                /* translators: %s: product name */
                $message = sprintf( __( 'You cannot add another "%s" to your cart.', 'woocommerce' ), $product_data->get_name() );

                /**
                 * Filters message about more than 1 product being added to cart.
                 *
                 * @since 4.5.0
                 * @param string     $message Message.
                 * @param WC_Product $product_data Product data.
                 */
                $message         = apply_filters( 'woocommerce_cart_product_cannot_add_another_message', $message, $product_data );
                $wp_button_class = wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '';

                throw new Exception( sprintf( '<a href="%s" class="button wc-forward%s">%s</a> %s', wc_get_cart_url(), esc_attr( $wp_button_class ), __( 'View cart', 'woocommerce' ), $message ) );
            }
        }

        if ( ! $product_data->is_purchasable() ) {
            $message = __( 'Sorry, this product cannot be purchased.', 'woocommerce' );
            /**
             * Filters message about product unable to be purchased.
             *
             * @since 3.8.0
             * @param string     $message Message.
             * @param WC_Product $product_data Product data.
             */
            $message = apply_filters( 'woocommerce_cart_product_cannot_be_purchased_message', $message, $product_data );
            throw new Exception( $message );
        }

        // Stock check - only check if we're managing stock and backorders are not allowed.
        if ( ! $product_data->is_in_stock() ) {
            /* translators: %s: product name */
            $message = sprintf( __( 'You cannot add &quot;%s&quot; to the cart because the product is out of stock.', 'woocommerce' ), $product_data->get_name() );

            /**
             * Filters message about product being out of stock.
             *
             * @since 4.5.0
             * @param string     $message Message.
             * @param WC_Product $product_data Product data.
             */
            $message = apply_filters( 'woocommerce_cart_product_out_of_stock_message', $message, $product_data );
            throw new Exception( $message );
        }

        if ( ! $product_data->has_enough_stock( $quantity ) ) {
            $stock_quantity = $product_data->get_stock_quantity();

            /* translators: 1: product name 2: quantity in stock */
            $message = sprintf( __( 'You cannot add that amount of &quot;%1$s&quot; to the cart because there is not enough stock (%2$s remaining).', 'woocommerce' ), $product_data->get_name(), wc_format_stock_quantity_for_display( $stock_quantity, $product_data ) );

            /**
             * Filters message about product not having enough stock.
             *
             * @since 4.5.0
             * @param string     $message Message.
             * @param WC_Product $product_data Product data.
             * @param int        $stock_quantity Quantity remaining.
             */
            $message = apply_filters( 'woocommerce_cart_product_not_enough_stock_message', $message, $product_data, $stock_quantity );

            throw new Exception( $message );
        }

        // Stock check - this time accounting for whats already in-cart.
        if ( $product_data->managing_stock() ) {
            $products_qty_in_cart = $this->get_cart_item_quantities();

            if ( isset( $products_qty_in_cart[ $product_data->get_stock_managed_by_id() ] ) && ! $product_data->has_enough_stock( $products_qty_in_cart[ $product_data->get_stock_managed_by_id() ] + $quantity ) ) {
                $stock_quantity         = $product_data->get_stock_quantity();
                $stock_quantity_in_cart = $products_qty_in_cart[ $product_data->get_stock_managed_by_id() ];
                $wp_button_class        = wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '';

                $message = sprintf(
                    '<a href="%s" class="button wc-forward%s">%s</a> %s',
                    wc_get_cart_url(),
                    esc_attr( $wp_button_class ),
                    __( 'View cart', 'woocommerce' ),
                    /* translators: 1: quantity in stock 2: current quantity */
                    sprintf( __( 'You cannot add that amount to the cart &mdash; we have %1$s in stock and you already have %2$s in your cart.', 'woocommerce' ), wc_format_stock_quantity_for_display( $stock_quantity, $product_data ), wc_format_stock_quantity_for_display( $stock_quantity_in_cart, $product_data ) )
                );

                /**
                 * Filters message about product not having enough stock accounting for what's already in the cart.
                 *
                 * @param string $message Message.
                 * @param WC_Product $product_data Product data.
                 * @param int $stock_quantity Quantity remaining.
                 * @param int $stock_quantity_in_cart
                 *
                 * @since 5.3.0
                 */
                $message = apply_filters( 'woocommerce_cart_product_not_enough_stock_already_in_cart_message', $message, $product_data, $stock_quantity, $stock_quantity_in_cart );

                throw new Exception( $message );
            }
        }

        // If cart_item_key is set, the item is already in the cart.
        if ( $cart_item_key ) {
            $new_quantity = $quantity + $this->custom_cart_contents[ $cart_item_key ]['quantity'];
            $this->set_quantity( $cart_item_key, $new_quantity, false );
        } else {
            $cart_item_key = $cart_id;

            // Add item after merging with $cart_item_data - hook to allow plugins to modify cart item.
            $this->custom_cart_contents[ $cart_item_key ] = apply_filters(
                'woocommerce_add_cart_item',
                array_merge(
                    $cart_item_data,
                    array(
                        'key'          => $cart_item_key,
                        'product_id'   => $product_id,
                        'variation_id' => $variation_id,
                        'variation'    => $variation,
                        'quantity'     => $quantity,
                        'data'         => $product_data,
                        'data_hash'    => wc_get_cart_item_data_hash( $product_data ),
                    )
                ),
                $cart_item_key
            );
        }

        $this->custom_cart_contents = apply_filters( 'woocommerce_cart_contents_changed', $this->custom_cart_contents );

        do_action( 'woocommerce_add_to_cart', $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data );

        return $cart_item_key;
    }

    // Helper function to find a product in the custom cart based on provided data
    private function find_product_in_custom_cart( $cart_id = false ) {
        if ( false !== $cart_id ) {
            if ( is_array( $this->custom_cart_contents ) && isset( $this->custom_cart_contents[ $cart_id ] ) ) {                
                return $cart_id;
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

add_action( 'wp_ajax_addProductToCart', 'addProductToCart');
function addProductToCart(){    
    $wrocpos_cart = new WRockpos_Cart();
    var_dump($wrocpos_cart->add_to_cart(250,1, 265, array('attribute_size' => 'XS', 'attribute_color' => 'Green')));
    echo '<pre>';    
    print_r($wrocpos_cart->get_custom_cart_contents());
    die();
}
