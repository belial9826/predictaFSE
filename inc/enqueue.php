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

    if (function_exists('is_account_page') && is_account_page()) {
        return true;
    }

    return false;
}

/**
 * Devuelve el nombre del bundle CSS unificado (base + vista) para el front.
 */
function predictafseGetFrontStyleBundle() {
    if (predictafseIsPronosticosContext()) {
        return 'pronosticos';
    }

    if (predictafseIsHomeContext()) {
        return 'home';
    }

    if (predictafseIsContactContext()) {
        return 'contact';
    }

    if (predictafseIsFaqContext()) {
        return 'faq';
    }

    if (predictafseIsPartidoContext()) {
        return 'partido';
    }

    if (predictafseIsWooCommerceContext()) {
        return 'woocommerce';
    }

    return 'main';
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
        'assets/css/editor.min.css',
    ));
}
add_action('after_setup_theme', 'predictafseRegisterEditorStyles', 20);

function predictafseEnqueueEditorAssets() {
    predictafseEnqueueStyleBundle('predictafse-icofont-editor', 'assets/icofont/icofont.min.css');
    predictafseEnqueueStyleBundle('predictafse-styles-editor', 'assets/css/editor.min.css', array('predictafse-icofont-editor'));
}
add_action('enqueue_block_editor_assets', 'predictafseEnqueueEditorAssets');

function predictafseEnqueueAssets() {
    $bundle = predictafseGetFrontStyleBundle();

    predictafseEnqueueStyleBundle('predictafse-icofont', 'assets/icofont/icofont.min.css');
    predictafseEnqueueStyleBundle(
        'predictafse-styles',
        'assets/css/' . $bundle . '.min.css',
        array('predictafse-icofont')
    );

    wp_enqueue_script(
        'predictafse-scripts',
        get_template_directory_uri() . '/assets/js/main.min.js',
        array('jquery'),
        predictafseGetAssetVersion('assets/js/main.min.js'),
        true
    );
}
add_action('wp_enqueue_scripts', 'predictafseEnqueueAssets');
