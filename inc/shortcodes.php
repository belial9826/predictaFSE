<?php

function predictafse_sc_user_header() {
    ob_start();
    ?>
    <div id="wrap-userHeader">
        <?php
        if (is_user_logged_in()) :
            $user = wp_get_current_user();
            // Escapar el nombre del usuario para prevenir XSS
            $display_name = esc_html($user->display_name);
            ?>
            <div class="wrap-userlogin has-plan">
                <p class="plan">Plan: Gratuito (1/2 Pronosticos)</p>
                <div class="wrap-user-actions">
                    <h5><?php echo $display_name; ?> <i class="icofont-duotone icofont-user"></i></h5>
                    <ul class="menu-user">
                        <li><a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">Perfil</a></li>
                        <li><a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>">Mis Suscripciones</a>
                        </li>
                        <li><a href="<?php echo esc_url(wc_logout_url(wc_get_page_permalink('myaccount'))); ?>">Salir</a>
                        </li>
                    </ul>
                </div>
            </div>
            <?php
        else :
            ?>
            <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">Iniciar sesión / Registrarse</a>
            <?php
        endif;
        ?>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('user_header', 'predictafse_sc_user_header');
