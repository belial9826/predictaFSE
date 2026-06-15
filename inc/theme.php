<?php

function predictafse_pattern_html($ruta_relativa) {
    $ruta = get_template_directory() . '/' . ltrim($ruta_relativa, '/');

    if (!file_exists($ruta) || !is_readable($ruta)) {
        return '';
    }

    return trim(file_get_contents($ruta));
}

function predictafse_register_pattern_cats() {
    if (!function_exists('register_block_pattern_category')) {
        return;
    }

    register_block_pattern_category(
        'predictafse-theme',
        array(
            'label' => __('Predicta FSE Theme', 'predictafse'),
        )
    );
}
add_action('init', 'predictafse_register_pattern_cats', 9);

function predictafse_register_patterns() {
    if (!function_exists('register_block_pattern')) {
        return;
    }

    $registry = WP_Block_Patterns_Registry::get_instance();

    $patrones = array(
        array(
            'slug'          => 'predictafse/contacto-pagina',
            'file'          => 'inc/patterns/contacto-pagina.html',
            'title'         => __('Contenido página de contacto', 'predictafse'),
            'description'   => __('Layout de contacto con info lateral y formulario CF7.', 'predictafse'),
            'keywords'      => array('contacto', 'contact', 'formulario', 'cf7', 'email'),
        ),
        array(
            'slug'          => 'predictafse/faq-pagina',
            'file'          => 'inc/patterns/faq-pagina.html',
            'title'         => __('Contenido página de FAQ', 'predictafse'),
            'description'   => __('Layout de preguntas frecuentes con acordeón editable.', 'predictafse'),
            'keywords'      => array('faq', 'preguntas', 'frecuentes', 'ayuda', 'soporte'),
        ),
        array(
            'slug'          => 'predictafse/pronosticos-pagina',
            'file'          => 'inc/patterns/pronosticos-pagina.html',
            'title'         => __('Contenido directorio de pronósticos', 'predictafse'),
            'description'   => __('Directorio completo de partidos con grid y paginación del CPT partido.', 'predictafse'),
            'keywords'      => array('pronosticos', 'partidos', 'futbol', 'directorio', 'pronosticos-de-futbol'),
        ),
    );

    foreach ($patrones as $patron) {
        if ($registry->is_registered($patron['slug'])) {
            continue;
        }

        $contenido = predictafse_pattern_html($patron['file']);

        if ($contenido === '') {
            continue;
        }

        register_block_pattern(
            $patron['slug'],
            array(
                'title'         => $patron['title'],
                'description'   => $patron['description'],
                'categories'    => array('predictafse-theme'),
                'keywords'      => $patron['keywords'],
                'postTypes'     => array('page'),
                'inserter'      => true,
                'viewportWidth' => 1280,
                'content'       => $contenido,
            )
        );
    }
}
add_action('init', 'predictafse_register_patterns', 11);

function predictafse_page_body_class($classes) {
    if (function_exists('predictafse_ctx_contact') && predictafse_ctx_contact()) {
        $classes[] = 'predictafse-contacto';
    }

    if (function_exists('predictafse_ctx_faq') && predictafse_ctx_faq()) {
        $classes[] = 'predictafse-faq';
    }

    if (function_exists('predictafse_ctx_pronosticos') && predictafse_ctx_pronosticos()) {
        $classes[] = 'predictafse-pronosticos';
    }

    return $classes;
}
add_filter('body_class', 'predictafse_page_body_class');
