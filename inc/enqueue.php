<?php
/**
 * Encolado de estilos y scripts para el tema Predicta FSE
 *
 * Front-end: una sola hoja de estilos del theme por vista (incluye base + vista).
 * icofont.min.css se mantiene aparte por ser fuente de iconos externa.
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

function predictafseTemplateSlugMatches($needles) {
    $slug = predictafseGetTemplateSlug();
    if ($slug === '') {
        return false;
    }

    foreach ((array) $needles as $needle) {
        if ($slug === $needle) {
            return true;
        }

        if ($slug === 'templates/' . $needle . '.html') {
            return true;
        }

        if (str_ends_with($slug, '/' . $needle . '.html')) {
            return true;
        }
    }

    return false;
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

function predictafseIsHomeContext() {
    if (predictafseIsWooCommerceContext()) {
        return false;
    }

    if (is_front_page()) {
        return true;
    }

    if (is_home()) {
        return true;
    }

    return predictafseTemplateSlugMatches(array('front-page', 'home'));
}

function predictafseIsContactContext() {
    if (!is_page() || is_front_page() || predictafseIsWooCommerceContext()) {
        return false;
    }

    if (predictafseTemplateSlugMatches('page-contacto')) {
        return true;
    }

    $post = get_queried_object();
    if ($post instanceof WP_Post && $post->post_name === 'contactenos') {
        return true;
    }

    return false;
}

function predictafseIsFaqContext() {
    if (!is_page() || is_front_page() || predictafseIsWooCommerceContext()) {
        return false;
    }

    if (predictafseTemplateSlugMatches('page-faq')) {
        return true;
    }

    $post = get_queried_object();
    if ($post instanceof WP_Post && in_array($post->post_name, array('faq', 'preguntas-frecuentes'), true)) {
        return true;
    }

    return false;
}

function predictafseIsPronosticosContext() {
    if (!is_page() || is_front_page() || predictafseIsWooCommerceContext()) {
        return false;
    }

    if (predictafseTemplateSlugMatches('page-pronosticos')) {
        return true;
    }

    $post = get_queried_object();
    if ($post instanceof WP_Post && in_array($post->post_name, array('pronosticos-de-futbol', 'pronosticos-futbol'), true)) {
        return true;
    }

    return false;
}

function predictafseIsPartidoContext() {
    if (predictafseIsWooCommerceContext()) {
        return false;
    }

    return is_singular('partido');
}

function predictafseGetFrontStyleBundle() {
    if (predictafseIsWooCommerceContext()) {
        return 'assets/css/woocommerce.min.css';
    }

    if (predictafseIsHomeContext()) {
        return 'assets/css/home.min.css';
    }

    if (predictafseIsContactContext()) {
        return 'assets/css/contact.min.css';
    }

    if (predictafseIsFaqContext()) {
        return 'assets/css/faq.min.css';
    }

    if (predictafseIsPronosticosContext()) {
        return 'assets/css/pronosticos.min.css';
    }

    if (predictafseIsPartidoContext()) {
        return 'assets/css/partido.min.css';
    }

    return 'assets/css/main.min.css';
}

function predictafseEnqueueStyleBundle($handle, $relative_path, $deps = array()) {
    wp_enqueue_style(
        $handle,
        get_template_directory_uri() . '/' . ltrim($relative_path, '/'),
        $deps,
        predictafseGetAssetVersion($relative_path)
    );
}

function predictafseEnqueueScripts() {
    wp_enqueue_script(
        'predictafse-scripts',
        get_template_directory_uri() . '/assets/js/main.min.js',
        array('jquery'),
        predictafseGetAssetVersion('assets/js/main.min.js'),
        true
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
    predictafseEnqueueStyleBundle('predictafse-home-editor', 'assets/css/home.min.css', array('predictafse-icofont-editor'));
    predictafseEnqueueStyleBundle('predictafse-contact-editor', 'assets/css/contact.min.css', array('predictafse-icofont-editor'));
    predictafseEnqueueStyleBundle('predictafse-faq-editor', 'assets/css/faq.min.css', array('predictafse-icofont-editor'));
    predictafseEnqueueStyleBundle('predictafse-pronosticos-editor', 'assets/css/pronosticos.min.css', array('predictafse-icofont-editor'));
    predictafseEnqueueStyleBundle('predictafse-partido-editor', 'assets/css/partido.min.css', array('predictafse-icofont-editor'));
    predictafseEnqueueStyleBundle('predictafse-woocommerce-editor', 'assets/css/woocommerce.min.css', array('predictafse-icofont-editor'));
}
add_action('enqueue_block_editor_assets', 'predictafseEnqueueEditorAssets');

function predictafseEnqueueAssets() {
    predictafseEnqueueStyleBundle('predictafse-icofont', 'assets/icofont/icofont.min.css');
    predictafseEnqueueStyleBundle(
        'predictafse-styles',
        predictafseGetFrontStyleBundle(),
        array('predictafse-icofont')
    );
    predictafseEnqueueScripts();
}
add_action('wp_enqueue_scripts', 'predictafseEnqueueAssets');
