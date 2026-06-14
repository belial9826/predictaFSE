<?php

function predictafseObtenerUrlPlanesSuscripcion() {
    return trailingslashit(home_url()) . '#planes-suscripcion';
}

function predictafseRedirigirCarritoCheckoutVacio() {
    if (is_admin() || wp_doing_ajax()) {
        return;
    }

    if (!function_exists('is_cart') || !function_exists('is_checkout') || !function_exists('WC')) {
        return;
    }

    if (!is_cart() && !is_checkout()) {
        return;
    }

    if (is_checkout() && function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-received')) {
        return;
    }

    if (!isset(WC()->cart) || !WC()->cart->is_empty()) {
        return;
    }

    // wp_safe_redirect elimina el fragmento (#); la URL se construye desde home_url().
    wp_redirect(predictafseObtenerUrlPlanesSuscripcion());
    exit;
}
add_action('template_redirect', 'predictafseRedirigirCarritoCheckoutVacio', 5);

function predictafseEvitarRedireccionCheckoutVacioWc() {
    return false;
}
add_filter('woocommerce_checkout_redirect_empty_cart', 'predictafseEvitarRedireccionCheckoutVacioWc');

function predictafseAgregarClaseBodyWooCommerce($classes) {
    if (!function_exists('is_cart') || !function_exists('is_checkout')) {
        return $classes;
    }

    if (is_cart() || is_checkout() || (function_exists('is_order_received_page') && is_order_received_page())) {
        $classes[] = 'predictafse-woocommerce';
    }

    if (is_cart()) {
        $classes[] = 'predictafse-cart';
    }

    if (is_checkout() && !(function_exists('is_order_received_page') && is_order_received_page())) {
        $classes[] = 'predictafse-checkout';
    }

    return $classes;
}
add_filter('body_class', 'predictafseAgregarClaseBodyWooCommerce');
