<?php
/*
Plugin Name: Wownero Woocommerce Gateway
Plugin URI: https://github.com/monero-integrations/monerowp
Description: Extends WooCommerce by adding a Wownero Gateway
Version: 3.0.0
Tested up to: 4.9.8
Author: mosu-forge, SerHack
Author URI: https://monerointegrations.com/
*/
// This code isn't for Dark Net Markets, please report them to Authority!

defined( 'ABSPATH' ) || exit;

// Constants, you can edit these if you fork this repo
define('WOWNERO_GATEWAY_MAINNET_EXPLORER_URL', 'https://explore.wownero.com/');
define('WOWNERO_GATEWAY_TESTNET_EXPLORER_URL', 'http://explorer.wowne.ro:8082/');
define('WOWNERO_GATEWAY_ADDRESS_PREFIX', 0x12);
define('WOWNERO_GATEWAY_ADDRESS_PREFIX_INTEGRATED', 0x13);
define('WOWNERO_GATEWAY_ATOMIC_UNITS', 11);
define('WOWNERO_GATEWAY_ATOMIC_UNIT_THRESHOLD', 10); // Amount under in atomic units payment is valid
define('WOWNERO_GATEWAY_DIFFICULTY_TARGET', 120);

// Do not edit these constants
define('WOWNERO_GATEWAY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WOWNERO_GATEWAY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WOWNERO_GATEWAY_ATOMIC_UNITS_POW', pow(10, WOWNERO_GATEWAY_ATOMIC_UNITS));
define('WOWNERO_GATEWAY_ATOMIC_UNITS_SPRINTF', '%.'.WOWNERO_GATEWAY_ATOMIC_UNITS.'f');

// Include our Gateway Class and register Payment Gateway with WooCommerce
add_action('plugins_loaded', 'wownero_init', 1);
function wownero_init() {

    // If the class doesn't exist (== WooCommerce isn't installed), return NULL
    if (!class_exists('WC_Payment_Gateway')) return;

    // If we made it this far, then include our Gateway Class
    require_once('include/class-wownero-gateway.php');

    // Create a new instance of the gateway so we have static variables set up
    new Wownero_Gateway($add_action=false);

    // Include our Admin interface class
    require_once('include/admin/class-wownero-admin-interface.php');

    add_filter('woocommerce_payment_gateways', 'wownero_gateway');
    function wownero_gateway($methods) {
        $methods[] = 'Wownero_Gateway';
        return $methods;
    }

    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wownero_payment');
    function wownero_payment($links) {
        $plugin_links = array(
            '<a href="'.admin_url('admin.php?page=wownero_gateway_settings').'">'.__('Settings', 'wownero_gateway').'</a>'
        );
        return array_merge($plugin_links, $links);
    }

    add_filter('cron_schedules', 'wownero_cron_add_one_minute');
    function wownero_cron_add_one_minute($schedules) {
        $schedules['one_minute'] = array(
            'interval' => 60,
            'display' => __('Once every minute', 'wownero_gateway')
        );
        return $schedules;
    }

    add_action('wp', 'wownero_activate_cron');
    function wownero_activate_cron() {
        if(!wp_next_scheduled('wownero_update_event')) {
            wp_schedule_event(time(), 'one_minute', 'wownero_update_event');
        }
    }

    add_action('wownero_update_event', 'wownero_update_event');
    function wownero_update_event() {
        Wownero_Gateway::do_update_event();
    }

    add_action('woocommerce_thankyou_'.Wownero_Gateway::get_id(), 'wownero_order_confirm_page');
    add_action('woocommerce_order_details_after_order_table', 'wownero_order_page');
    add_action('woocommerce_email_after_order_table', 'wownero_order_email');

    function wownero_order_confirm_page($order_id) {
        Wownero_Gateway::customer_order_page($order_id);
    }
    function wownero_order_page($order) {
        if(!is_wc_endpoint_url('order-received'))
            Wownero_Gateway::customer_order_page($order);
    }
    function wownero_order_email($order) {
        Wownero_Gateway::customer_order_email($order);
    }

    add_action('wc_ajax_wownero_gateway_payment_details', 'wownero_get_payment_details_ajax');
    function wownero_get_payment_details_ajax() {
        Wownero_Gateway::get_payment_details_ajax();
    }

    add_filter('woocommerce_currencies', 'wownero_add_currency');
    function wownero_add_currency($currencies) {
        $currencies['Wownero'] = __('Wownero', 'wownero_gateway');
        return $currencies;
    }

    add_filter('woocommerce_currency_symbol', 'wownero_add_currency_symbol', 10, 2);
    function wownero_add_currency_symbol($currency_symbol, $currency) {
        switch ($currency) {
        case 'Wownero':
            $currency_symbol = 'WOW';
            break;
        }
        return $currency_symbol;
    }

    if(Wownero_Gateway::use_wownero_price()) {

        // This filter will replace all prices with amount in Wownero (live rates)
        add_filter('wc_price', 'wownero_live_price_format', 10, 3);
        function wownero_live_price_format($price_html, $price_float, $args) {
            if(!isset($args['currency']) || !$args['currency']) {
                global $woocommerce;
                $currency = strtoupper(get_woocommerce_currency());
            } else {
                $currency = strtoupper($args['currency']);
            }
            return Wownero_Gateway::convert_wc_price($price_float, $currency);
        }

        // These filters will replace the live rate with the exchange rate locked in for the order
        // We must be careful to hit all the hooks for price displays associated with an order,
        // else the exchange rate can change dynamically (which it should for an order)
        add_filter('woocommerce_order_formatted_line_subtotal', 'wownero_order_item_price_format', 10, 3);
        function wownero_order_item_price_format($price_html, $item, $order) {
            return Wownero_Gateway::convert_wc_price_order($price_html, $order);
        }

        add_filter('woocommerce_get_formatted_order_total', 'wownero_order_total_price_format', 10, 2);
        function wownero_order_total_price_format($price_html, $order) {
            return Wownero_Gateway::convert_wc_price_order($price_html, $order);
        }

        add_filter('woocommerce_get_order_item_totals', 'wownero_order_totals_price_format', 10, 3);
        function wownero_order_totals_price_format($total_rows, $order, $tax_display) {
            foreach($total_rows as &$row) {
                $price_html = $row['value'];
                $row['value'] = Wownero_Gateway::convert_wc_price_order($price_html, $order);
            }
            return $total_rows;
        }

    }

    add_action('wp_enqueue_scripts', 'wownero_enqueue_scripts');
    function wownero_enqueue_scripts() {
        if(Wownero_Gateway::use_wownero_price())
            wp_dequeue_script('wc-cart-fragments');
        if(Wownero_Gateway::use_qr_code())
            wp_enqueue_script('wownero-qr-code', WOWNERO_GATEWAY_PLUGIN_URL.'assets/js/qrcode.min.js');

        wp_enqueue_script('wownero-clipboard-js', WOWNERO_GATEWAY_PLUGIN_URL.'assets/js/clipboard.min.js');
        wp_enqueue_script('wownero-gateway', WOWNERO_GATEWAY_PLUGIN_URL.'assets/js/wownero-gateway-order-page.js');
        wp_enqueue_style('wownero-gateway', WOWNERO_GATEWAY_PLUGIN_URL.'assets/css/wownero-gateway-order-page.css');
    }

    // [wownero-price currency="USD"]
    // currency: BTC, GBP, etc
    // if no none, then default store currency
    function wownero_price_func( $atts ) {
        global  $woocommerce;
        $a = shortcode_atts( array(
            'currency' => get_woocommerce_currency()
        ), $atts );

        $currency = strtoupper($a['currency']);
        $rate = Wownero_Gateway::get_live_rate($currency);
        if($currency == 'BTC')
            $rate_formatted = sprintf('%.8f', $rate / 1e8);
        else
            $rate_formatted = sprintf('%.5f', $rate / 1e8);

        return "<span class=\"wownero-price\">1 XMR = $rate_formatted $currency</span>";
    }
    add_shortcode('wownero-price', 'wownero_price_func');


    // [wownero-accepted-here]
    function wownero_accepted_func() {
        return '<img src="'.WOWNERO_GATEWAY_PLUGIN_URL.'assets/images/wownero-accepted-here.png" />';
    }
    add_shortcode('wownero-accepted-here', 'wownero_accepted_func');

}

register_deactivation_hook(__FILE__, 'wownero_deactivate');
function wownero_deactivate() {
    $timestamp = wp_next_scheduled('wownero_update_event');
    wp_unschedule_event($timestamp, 'wownero_update_event');
}

register_activation_hook(__FILE__, 'wownero_install');
function wownero_install() {
    global $wpdb;
    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . "wownero_gateway_quotes";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               order_id BIGINT(20) UNSIGNED NOT NULL,
               payment_id VARCHAR(100) DEFAULT '' NOT NULL,
               currency VARCHAR(6) DEFAULT '' NOT NULL,
               rate BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               amount BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               paid TINYINT NOT NULL DEFAULT 0,
               confirmed TINYINT NOT NULL DEFAULT 0,
               pending TINYINT NOT NULL DEFAULT 1,
               created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
               PRIMARY KEY (order_id)
               ) $charset_collate;";
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . "wownero_gateway_quotes_txids";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
               payment_id VARCHAR(100) DEFAULT '' NOT NULL,
               txid VARCHAR(64) DEFAULT '' NOT NULL,
               amount BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               height MEDIUMINT UNSIGNED NOT NULL DEFAULT 0,
               PRIMARY KEY (id),
               UNIQUE KEY (payment_id, txid, amount)
               ) $charset_collate;";
        dbDelta($sql);
    }

    $table_name = $wpdb->prefix . "wownero_gateway_live_rates";
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
               currency VARCHAR(6) DEFAULT '' NOT NULL,
               rate BIGINT UNSIGNED DEFAULT 0 NOT NULL,
               updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
               PRIMARY KEY (currency)
               ) $charset_collate;";
        dbDelta($sql);
    }
}
