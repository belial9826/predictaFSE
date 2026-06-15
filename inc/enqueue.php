<?php
/**
 * Encolado de estilos y scripts para el tema Predicta FSE
 */

function predictafse_get_asset_version($relative_path) {
    $file = get_template_directory() . '/' . ltrim($relative_path, '/');
    if (file_exists($file)) {
        return (string) filemtime($file);
    }
    return '1.0.0';
}

function predictafse_get_template_slug() {
    if (is_singular()) {
        $slug = get_page_template_slug(get_queried_object_id());
        if (!empty($slug)) {
            return $slug;
        }
    }

    if (is_front_page()) {
        return 'front-page';
    }

    if (is_home()) {
        return 'home';
    }

    return '';
}

function predictafse_is_home_context() {
    if (is_front_page() || is_home()) {
        return true;
    }

    $slug = predictafse_get_template_slug();
    return in_array($slug, array('front-page', 'home'), true);
}

function predictafse_is_contact_context() {
    if (!is_page()) {
        return false;
    }

    $slug = predictafse_get_template_slug();
    if ($slug === 'page-contacto') {
        return true;
    }

    $post = get_queried_object();
    if ($post instanceof WP_Post && $post->post_name === 'contactenos') {
        return true;
    }

    return false;
}

function predictafse_is_faq_context() {
    if (!is_page()) {
        return false;
    }

    $slug = predictafse_get_template_slug();
    if ($slug === 'page-faq') {
        return true;
    }

    $post = get_queried_object();
    if ($post instanceof WP_Post && in_array($post->post_name, array('faq', 'preguntas-frecuentes'), true)) {
        return true;
    }

    return false;
}

function predictafse_is_pronosticos_context() {
    if (!is_page()) {
        return false;
    }

    $slug = predictafse_get_template_slug();
    if ($slug === 'page-pronosticos') {
        return true;
    }

    $post = get_queried_object();
    if ($post instanceof WP_Post && in_array($post->post_name, array('pronosticos-de-futbol', 'pronosticos-futbol'), true)) {
        return true;
    }

    return false;
}

function predictafse_is_partido_context() {
    return is_singular('partido');
}

function predictafse_is_woocommerce_context() {
    if (!function_exists('is_cart') || !function_exists('is_checkout')) {
        return false;
    }

    if (is_cart() || is_checkout()) {
        return true;
    }

    if (function_exists('is_order_received_page') && is_order_received_page()) {
        return true;
    }

    return false;
}

function predictafse_enqueue_style_bundle($handle, $relative_path, $deps = array()) {
    wp_enqueue_style(
        $handle,
        get_template_directory_uri() . '/' . ltrim($relative_path, '/'),
        $deps,
        predictafse_get_asset_version($relative_path)
    );
}

function predictafse_register_editor_styles() { 
    add_editor_style(array(
        'assets/icofont/icofont.min.css',
        'assets/css/main.min.css',
        'assets/css/home.min.css',
        'assets/css/contact.min.css',
        'assets/css/faq.min.css',
        'assets/css/pronosticos.min.css',
        'assets/css/partido.min.css',
        'assets/css/woocommerce.min.css',
    ));
}
add_action('after_setup_theme', 'predictafse_register_editor_styles', 20);

function predictafse_enqueue_editor_assets() {
    predictafse_enqueue_style_bundle('predictafse-icofont-editor', 'assets/icofont/icofont.min.css');
    predictafse_enqueue_style_bundle('predictafse-styles-editor', 'assets/css/main.min.css', array('predictafse-icofont-editor'));
    predictafse_enqueue_style_bundle('predictafse-home-editor', 'assets/css/home.min.css', array('predictafse-styles-editor'));
    predictafse_enqueue_style_bundle('predictafse-contact-editor', 'assets/css/contact.min.css', array('predictafse-styles-editor'));
    predictafse_enqueue_style_bundle('predictafse-faq-editor', 'assets/css/faq.min.css', array('predictafse-styles-editor'));
    predictafse_enqueue_style_bundle('predictafse-pronosticos-editor', 'assets/css/pronosticos.min.css', array('predictafse-styles-editor', 'predictafse-home-editor'));
    predictafse_enqueue_style_bundle('predictafse-partido-editor', 'assets/css/partido.min.css', array('predictafse-styles-editor'));
}
add_action('enqueue_block_editor_assets', 'predictafse_enqueue_editor_assets');

function predictafse_enqueue_assets() {
    predictafse_enqueue_style_bundle('predictafse-icofont', 'assets/icofont/icofont.min.css');
    predictafse_enqueue_style_bundle('predictafse-styles', 'assets/css/main.min.css', array('predictafse-icofont'));

    if (predictafse_is_home_context()) {
        predictafse_enqueue_style_bundle('predictafse-home', 'assets/css/home.min.css', array('predictafse-styles'));
    }

    if (predictafse_is_contact_context()) {
        predictafse_enqueue_style_bundle('predictafse-contact', 'assets/css/contact.min.css', array('predictafse-styles'));
    }

    if (predictafse_is_faq_context()) {
        predictafse_enqueue_style_bundle('predictafse-faq', 'assets/css/faq.min.css', array('predictafse-styles'));
    }

    if (predictafse_is_pronosticos_context()) {
        predictafse_enqueue_style_bundle('predictafse-home', 'assets/css/home.min.css', array('predictafse-styles'));
        predictafse_enqueue_style_bundle('predictafse-pronosticos', 'assets/css/pronosticos.min.css', array('predictafse-styles', 'predictafse-home'));
    }

    if (predictafse_is_partido_context()) {
        predictafse_enqueue_style_bundle('predictafse-partido', 'assets/css/partido.min.css', array('predictafse-styles'));
    }

    if (predictafse_is_woocommerce_context()) {
        predictafse_enqueue_style_bundle('predictafse-woocommerce', 'assets/css/woocommerce.min.css', array('predictafse-styles'));
    }

    wp_enqueue_script(
        'predictafse-scripts',
        get_template_directory_uri() . '/assets/js/main.min.js',
        array('jquery'),
        predictafse_get_asset_version('assets/js/main.min.js'),
        true
    );
}
add_action('wp_enqueue_scripts', 'predictafse_enqueue_assets');
