/**
 * Script de Comportamiento e Interacciones - FutbolIA Theme
 * Estándar jQuery para compatibilidad y rendimiento ligero
 */

jQuery(document).ready(function($) {
    const $userActions = '.wrap-userlogin.has-plan .wrap-user-actions';
    const mqDesktop = window.matchMedia('(min-width: 768px)');

    // Desktop: hover en trigger + dropdown
    $(document).on('mouseenter', $userActions, function() {
        if (!mqDesktop.matches) {
            return;
        }
        $(this).find('.menu-user').addClass('open');
    });

    $(document).on('mouseleave', $userActions, function() {
        if (!mqDesktop.matches) {
            return;
        }
        $(this).find('.menu-user').removeClass('open');
    });

    // Móvil: toggle al pulsar el avatar / nombre

    if( !mqDesktop.matches ) {
        $('#wrap-userHeader').appendTo('.wp-block-navigation__container.is-responsive.wp-block-navigation');
    }

    $(document).on('click', '.FSE-header .wp-block-navigation-submenu .wp-block-navigation-submenu__toggle', function(e) {
        if (mqDesktop.matches) {
            return;
        }
        e.preventDefault();
        e.stopPropagation();
        
        $(this).closest('.wp-block-navigation-submenu').find('.wp-block-navigation-submenu').toggleClass('is-open');
    });


    $(document).on('click', '.wrap-userlogin.has-plan .wrap-user-actions h5', function(e) {
        if (mqDesktop.matches) {
            return;
        }
        e.preventDefault();
        e.stopPropagation();
        $(this).closest('.wrap-user-actions').find('.menu-user').toggleClass('open');
    });

    // Cerrar menú de usuario al pulsar fuera
    $(document).on('click', function(e) {
        if (!$(e.target).closest($userActions).length) {
            $($userActions).find('.menu-user').removeClass('open');
        }
    });

    // FAQ: acordeón — clase .open en el patrón (sin atributo open en el HTML)
    var $faqPage = $('.faq-page');

    if ($faqPage.length) {
        $faqPage.find('.faq-item.open').prop('open', true);

        $faqPage.on('toggle', '.faq-item', function() {
            var $item = $(this);

            if (this.open) {
                $item.addClass('open');
                $faqPage.find('.faq-item').not(this).each(function() {
                    this.open = false;
                    $(this).removeClass('open');
                });
            } else {
                $item.removeClass('open');
            }
        });
    }
});
