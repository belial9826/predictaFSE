<?php

function predictafseObtenerContenidoPatronHtml($ruta_relativa) {
    $ruta = get_template_directory() . '/' . ltrim($ruta_relativa, '/');

    if (!file_exists($ruta) || !is_readable($ruta)) {
        return '';
    }

    return trim(file_get_contents($ruta));
}

function predictafseRegistrarCategoriasPatrones() {
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
add_action('init', 'predictafseRegistrarCategoriasPatrones', 9);

function predictafseRegistrarPatrones() {
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

        $contenido = predictafseObtenerContenidoPatronHtml($patron['file']);

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
add_action('init', 'predictafseRegistrarPatrones', 11);

function predictafseBodyClassPaginasTema($classes) {
    if (function_exists('predictafseIsContactContext') && predictafseIsContactContext()) {
        $classes[] = 'predictafse-contacto';
    }

    if (function_exists('predictafseIsFaqContext') && predictafseIsFaqContext()) {
        $classes[] = 'predictafse-faq';
    }

    if (function_exists('predictafseIsPronosticosContext') && predictafseIsPronosticosContext()) {
        $classes[] = 'predictafse-pronosticos';
    }

    return $classes;
}
add_filter('body_class', 'predictafseBodyClassPaginasTema');
