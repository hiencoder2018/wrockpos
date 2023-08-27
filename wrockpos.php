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
    //wp_enqueue_style( 'rockpos-style', plugin_dir_url( __FILE__ ) . 'build/index.css' );    
    wp_enqueue_script( 'rockpos-script', plugin_dir_url(__FILE__) . 'frontend/dist/assets/index.js', array( 'wp-element' ), '1.0.0', true );
}
