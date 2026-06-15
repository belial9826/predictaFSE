<?php

function predictafse_obtener_url_planes_suscripcion() {
    return trailingslashit(home_url()) . '#planes-suscripcion';
}

function predictafse_redirigir_carrito_checkout_vacio() {
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
    wp_redirect(predictafse_obtener_url_planes_suscripcion());
    exit;
}
add_action('template_redirect', 'predictafse_redirigir_carrito_checkout_vacio', 5);

function predictafse_evitar_redireccion_checkout_vacio_wc() {
    return false;
}
add_filter('woocommerce_checkout_redirect_empty_cart', 'predictafse_evitar_redireccion_checkout_vacio_wc');

function predictafse_agregar_clase_body_woocommerce($classes) {
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
add_filter('body_class', 'predictafse_agregar_clase_body_woocommerce');
