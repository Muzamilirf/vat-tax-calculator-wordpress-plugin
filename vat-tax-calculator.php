<?php
/**
 * Plugin Name: VAT Tax Calculator
 * Plugin URI: https://www.linkedin.com/in/muzamilirf/
 * Description: A plugin to calculate VAT during checkout for countries that use VAT in WooCommerce.
 * Version: 1.1
 * Author: Muzamil Ahmad
 * Author URI: https://www.linkedin.com/in/muzamilirf/
 * License: GPL2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// List of countries using VAT
function vat_tax_calculator_vat_countries() {
    return [
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 
        'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 
        'SE', 'SI', 'SK'
    ];
}

// Add VAT field during checkout
add_action('woocommerce_cart_calculate_fees', 'vat_tax_calculator_add_vat_fee');
function vat_tax_calculator_add_vat_fee($cart) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    // Get the customer's billing country
    $billing_country = WC()->customer->get_billing_country();

    // Check if the country is in the VAT countries list
    $vat_countries = vat_tax_calculator_vat_countries();
    if (!in_array($billing_country, $vat_countries)) {
        return; // Do not add VAT if the country is not in the list
    }

    // VAT percentage (can be set in settings)
    $vat_percentage = get_option('vat_tax_calculator_percentage', 15);

    // Calculate VAT
    $vat_fee = ($cart->get_subtotal() * $vat_percentage) / 100;

    // Add VAT fee to the cart
    $cart->add_fee(__('VAT Tax', 'vat-tax-calculator'), $vat_fee, true);
}

// Add settings page for VAT configuration
add_action('admin_menu', 'vat_tax_calculator_create_menu');
function vat_tax_calculator_create_menu() {
    add_menu_page(
        __('VAT Tax Calculator Settings', 'vat-tax-calculator'),
        __('VAT Settings', 'vat-tax-calculator'),
        'manage_options',
        'vat-tax-calculator',
        'vat_tax_calculator_settings_page',
        'dashicons-calculator',
        110
    );
}

// VAT settings page content
function vat_tax_calculator_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('VAT Tax Calculator Settings', 'vat-tax-calculator'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('vat_tax_calculator_settings_group');
            do_settings_sections('vat-tax-calculator');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings
add_action('admin_init', 'vat_tax_calculator_register_settings');
function vat_tax_calculator_register_settings() {
    register_setting('vat_tax_calculator_settings_group', 'vat_tax_calculator_percentage');
    add_settings_section(
        'vat_tax_calculator_main_section',
        __('General Settings', 'vat-tax-calculator'),
        null,
        'vat-tax-calculator'
    );
    add_settings_field(
        'vat_tax_calculator_percentage',
        __('VAT Percentage', 'vat-tax-calculator'),
        'vat_tax_calculator_percentage_field',
        'vat-tax-calculator',
        'vat_tax_calculator_main_section'
    );
}

// VAT percentage field
function vat_tax_calculator_percentage_field() {
    $value = get_option('vat_tax_calculator_percentage', 15);
    echo '<input type="number" name="vat_tax_calculator_percentage" value="' . esc_attr($value) . '" />';
}
