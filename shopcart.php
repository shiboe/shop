<?php

/*
 * Plugin Name: Shopcart
 * Plugin URI: http://www.shiboe.com/projects/wordpress/shopcart
 * Description: flexible shopping cart interfacing with amazon FPS
 * Author: Stephen Cave
 * Author URI: http://www.shiboe.com
 * Version: 0.4
 */

//GLOBALS

define("TEMP_IMAGE_LOC","/home1/retrogra/public_html/paperloveanddreams/images/shopcart/");
define("TEMP_IMAGE_URL","/images/shopcart/");

//INCLUDES

include_once('includes/product.php');
include_once('includes/order.php');

wp_enqueue_script("shopcart_order-script", plugin_dir_url(__FILE__) . "js/shopcart_order.js");

//HOOKS

// saving for future - registering plugin settings
function shopcart_register_settings()
{
    //register_setting('shopcart_manage_group','shopcart_manage');
}
add_action('admin_init','shopcart_register_settings');

// shortcode for menu
function shopcart_menu_implement()
{
    ob_start();
    include_once("includes/menu.php");
    echo ob_get_clean();
}
//add_shortcode( 'shopcart_menu', 'shopcart_menu_implement' );

// shortcode implementation for shopping front pages
function shopcart_implement()
{
    wp_enqueue_style('shopcart-css', plugin_dir_url(__FILE__) . 'css/shopcart.css');
    wp_enqueue_script("shopcart-script", plugin_dir_url(__FILE__) . "js/shopcart.js");
    wp_enqueue_script("shopcart_order-script", plugin_dir_url(__FILE__) . "js/shopcart_order.js");

    $category = str_replace( "_"," ",get_query_var("shopcart_category") );
    $product = get_query_var("shopcart_product");

    ob_start();

    if($product)include_once("front/productPage.php");
    else if($category)include_once("front/categoryPage.php");
    else include_once("front/shopPage.php");

    return ob_get_clean();
}
add_shortcode( 'shopcart', 'shopcart_implement' );

// shopcart checkout shortcode
function shopcart_checkout_implement()
{
    wp_enqueue_style('shopcart-css', plugin_dir_url(__FILE__) . 'css/shopcart.css');
    wp_enqueue_script("shopcart-script", plugin_dir_url(__FILE__) . "js/shopcart.js");
    wp_enqueue_script("shopcart_order-script", plugin_dir_url(__FILE__) . "js/shopcart_order.js");

    include_once("includes/amazonStandardButton/StandardButton.php");

    ob_start();

    include_once("front/checkoutPage.php");

    return ob_get_clean();
}
add_shortcode( 'shopcart_checkout', 'shopcart_checkout_implement' );

// shopcart success shortcode - processing
function shopcart_success_implement()
{
    wp_enqueue_style('shopcart-css', plugin_dir_url(__FILE__) . 'css/shopcart.css');
    wp_enqueue_script("shopcart-script", plugin_dir_url(__FILE__) . "js/shopcart.js");
    wp_enqueue_script("shopcart_order-script", plugin_dir_url(__FILE__) . "js/shopcart_order.js");

    $getvars = $_GET;

    //include_once('includes/SignatureUtilsForOutbound.php');
    include_once( "includes/amazonVerify/.config.inc.php");
    include_once( "includes/amazonVerify/SignatureUtilsForOutbound.php");
    include_once("includes/order.php");

    ob_start();

    include_once("front/successPage.php");

    return ob_get_clean();
}
add_shortcode( 'shopcart_success', 'shopcart_success_implement' );

// building menu for admin
function shopcart_add_menu_links()
{
    include_once('admin/settingsPage.php');
    add_menu_page('Shopcart settings','Shopcart','edit_pages','shopcart','shopcart_settingsPage');

    include_once('admin/addProductPage.php');
    add_submenu_page('shopcart','shopcart - Add Product','Add Product','edit_pages','shopcart_add','shopcart_addProductPage');

    include_once('admin/manageProductsPage.php');
    add_submenu_page('shopcart','shopcart - Manage Products','Manage Products','edit_pages','shopcart_manage','shopcart_manageProductsPage');

    include_once('admin/viewOrdersPage.php');
    add_submenu_page('shopcart','shopcart - View Orders','View Orders','edit_pages','shopcart_viewOrders','shopcart_viewOrdersPage');

    //include_once('admin/deleteProductPage.php');
    //add_submenu_page('shopcart','shopcart - Delete Product','Delete Product','edit_pages','shopcart_delete','shopcart_deleteProductsPage');
}
add_action('admin_menu',"shopcart_add_menu_links");

//interpret shop url with variables instead of subpages
function add_shopcart_vars($qvars) {
    $qvars[] = 'shopcart_category';
    $qvars[] = 'shopcart_product';
    return $qvars;
}
add_filter('query_vars', 'add_shopcart_vars');

function shopcart_shop_rewrite() {
    add_rewrite_rule('shop/([^/]+)/?$', 'index.php?pagename=shop&shopcart_category=$matches[1]','top');
    add_rewrite_rule('shop/([^/]+)/([^/]+)/?$', 'index.php?pagename=shop&shopcart_category=$matches[1]&shopcart_product=$matches[2]','top');
}
add_action('init', 'shopcart_shop_rewrite');