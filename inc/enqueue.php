<?php
/**
 * Encolado de estilos y scripts para el tema Predicta FSE
 */

function predictafseGetAssetVersion($relative_path) {
    $file = get_template_directory() . '/' . ltrim($relative_path, '/');
    if (file_exists($file)) {
        return (string) filemtime($file);
    }
    return '1.0.0';
}

function predictafseGetTemplateSlug() {
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

function predictafseIsHomeContext() {
    if (is_front_page() || is_home()) {
        return true;
    }

    $slug = predictafseGetTemplateSlug();
    return in_array($slug, array('front-page', 'home'), true);
}

function predictafseIsContactContext() {
    if (!is_page()) {
        return false;
    }

    $slug = predictafseGetTemplateSlug();
    if ($slug === 'page-contacto') {
        return true;
    }

    $post = get_queried_object();
    if ($post instanceof WP_Post && $post->post_name === 'contactenos') {
        return true;
    }

    return false;
}

function predictafseIsFaqContext() {
    if (!is_page()) {
        return false;
    }

    $slug = predictafseGetTemplateSlug();
    if ($slug === 'page-faq') {
        return true;
    }

    $post = get_queried_object();
    if ($post instanceof WP_Post && in_array($post->post_name, array('faq', 'preguntas-frecuentes'), true)) {
        return true;
    }

    return false;
}

function predictafseIsPronosticosContext() {
    if (!is_page()) {
        return false;
    }

    $slug = predictafseGetTemplateSlug();
    if ($slug === 'page-pronosticos') {
        return true;
    }

    $post = get_queried_object();
    if ($post instanceof WP_Post && in_array($post->post_name, array('pronosticos-de-futbol', 'pronosticos-futbol'), true)) {
        return true;
    }

    return false;
}

function predictafseIsPartidoContext() {
    return is_singular('partido');
}

function predictafseIsWooCommerceContext() {
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

function predictafseEnqueueStyleBundle($handle, $relative_path, $deps = array()) {
    wp_enqueue_style(
        $handle,
        get_template_directory_uri() . '/' . ltrim($relative_path, '/'),
        $deps,
        predictafseGetAssetVersion($relative_path)
    );
}

function predictafseRegisterEditorStyles() {
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
add_action('after_setup_theme', 'predictafseRegisterEditorStyles', 20);

function predictafseEnqueueEditorAssets() {
    predictafseEnqueueStyleBundle('predictafse-icofont-editor', 'assets/icofont/icofont.min.css');
    predictafseEnqueueStyleBundle('predictafse-styles-editor', 'assets/css/main.min.css', array('predictafse-icofont-editor'));
    predictafseEnqueueStyleBundle('predictafse-home-editor', 'assets/css/home.min.css', array('predictafse-styles-editor'));
    predictafseEnqueueStyleBundle('predictafse-contact-editor', 'assets/css/contact.min.css', array('predictafse-styles-editor'));
    predictafseEnqueueStyleBundle('predictafse-faq-editor', 'assets/css/faq.min.css', array('predictafse-styles-editor'));
    predictafseEnqueueStyleBundle('predictafse-pronosticos-editor', 'assets/css/pronosticos.min.css', array('predictafse-styles-editor', 'predictafse-home-editor'));
    predictafseEnqueueStyleBundle('predictafse-partido-editor', 'assets/css/partido.min.css', array('predictafse-styles-editor'));
}
add_action('enqueue_block_editor_assets', 'predictafseEnqueueEditorAssets');

function predictafseEnqueueAssets() {
    predictafseEnqueueStyleBundle('predictafse-icofont', 'assets/icofont/icofont.min.css');
    predictafseEnqueueStyleBundle('predictafse-styles', 'assets/css/main.min.css', array('predictafse-icofont'));

    if (predictafseIsHomeContext()) {
        predictafseEnqueueStyleBundle('predictafse-home', 'assets/css/home.min.css', array('predictafse-styles'));
    }

    if (predictafseIsContactContext()) {
        predictafseEnqueueStyleBundle('predictafse-contact', 'assets/css/contact.min.css', array('predictafse-styles'));
    }

    if (predictafseIsFaqContext()) {
        predictafseEnqueueStyleBundle('predictafse-faq', 'assets/css/faq.min.css', array('predictafse-styles'));
    }

    if (predictafseIsPronosticosContext()) {
        predictafseEnqueueStyleBundle('predictafse-home', 'assets/css/home.min.css', array('predictafse-styles'));
        predictafseEnqueueStyleBundle('predictafse-pronosticos', 'assets/css/pronosticos.min.css', array('predictafse-styles', 'predictafse-home'));
    }

    if (predictafseIsPartidoContext()) {
        predictafseEnqueueStyleBundle('predictafse-partido', 'assets/css/partido.min.css', array('predictafse-styles'));
    }

    if (predictafseIsWooCommerceContext()) {
        predictafseEnqueueStyleBundle('predictafse-woocommerce', 'assets/css/woocommerce.min.css', array('predictafse-styles'));
    }

    wp_enqueue_script(
        'predictafse-scripts',
        get_template_directory_uri() . '/assets/js/main.min.js',
        array('jquery'),
        predictafseGetAssetVersion('assets/js/main.min.js'),
        true
    );
}
add_action('wp_enqueue_scripts', 'predictafseEnqueueAssets');
