<?php

function predictafse_obtener_contenido_patron_html($ruta_relativa) {
    $ruta = get_template_directory() . '/' . ltrim($ruta_relativa, '/');

    if (!file_exists($ruta) || !is_readable($ruta)) {
        return '';
    }

    return trim(file_get_contents($ruta));
}

function predictafse_registrar_categorias_patrones() {
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
add_action('init', 'predictafse_registrar_categorias_patrones', 9);

function predictafse_registrar_patrones() {
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

        $contenido = predictafse_obtener_contenido_patron_html($patron['file']);

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
add_action('init', 'predictafse_registrar_patrones', 11);

function predictafse_body_class_paginas_tema($classes) {
    if (function_exists('predictafse_is_contact_context') && predictafse_is_contact_context()) {
        $classes[] = 'predictafse-contacto';
    }

    if (function_exists('predictafse_is_faq_context') && predictafse_is_faq_context()) {
        $classes[] = 'predictafse-faq';
    }

    if (function_exists('predictafse_is_pronosticos_context') && predictafse_is_pronosticos_context()) {
        $classes[] = 'predictafse-pronosticos';
    }

    return $classes;
}
add_filter('body_class', 'predictafse_body_class_paginas_tema');
