<?php

/**
 * Incluye todos los archivos PHP de la carpeta 'inc'
*/

$mi_tema_inc_dir = get_template_directory() . '/inc/';
$mi_tema_files = glob( $mi_tema_inc_dir . '*.php' );

if ( ! empty( $mi_tema_files ) && is_array( $mi_tema_files ) ) {
    foreach ( $mi_tema_files as $file_path ) {
        // Por seguridad, verificamos que sea un archivo real antes de incluirlo
        if ( file_exists( $file_path ) && is_file( $file_path ) ) {
            require_once $file_path;
        }
    }
}


function predictafse_setup() {
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
    
    // Habilitar soporte para estilos en el editor del backend (CSS en inc/enqueue.php)
    add_theme_support( 'editor-styles' );

    // Habilitar etiquetas de título automáticas en el <head>
    add_theme_support( 'title-tag' );

    // Habilitar soporte para imágenes destacadas (Post Thumbnails)
    add_theme_support( 'post-thumbnails' );

    // Habilitar marcado HTML5 semántico
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );

    // Soporte para alineaciones anchas y completas (wide y full)
    add_theme_support( 'align-wide' );

    // Soporte para incrustaciones responsivas (videos y otros embeds)
    add_theme_support( 'responsive-embeds' );

    // Soporte para estilos de bloques por defecto de WordPress
    add_theme_support( 'wp-block-styles' );

    // Soporte para logotipo personalizado (compatibilidad clásica)
    add_theme_support( 'custom-logo', array(
        'height'      => 100,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
    ) );

    // Registrar ubicaciones de menú (útil para menús clásicos y compatibilidad FSE)
    register_nav_menus( array(
        'primary' => esc_html__( 'Menú Principal', 'predictafse' ),
        'footer'  => esc_html__( 'Menú del Pie de Página', 'predictafse' ),
    ) );
}
add_action( 'after_setup_theme', 'predictafse_setup' );

// Registrar áreas de widgets (sidebar clásica para compatibilidad con plugins)
function predictafse_register_widgets() {
    register_sidebar( array(
        'name'          => esc_html__( 'Barra Lateral Principal', 'predictafse' ),
        'id'            => 'sidebar-1',
        'description'   => esc_html__( 'Añade widgets clásicos aquí.', 'predictafse' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ) );
}
add_action( 'widgets_init', 'predictafse_register_widgets' );

