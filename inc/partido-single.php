<?php

function predictafseUserHasRecentFutbolOrders($user_id = 0) {
    if (!function_exists('wc_get_orders')) {
        return false;
    }

    if (empty($user_id)) {
        $user_id = get_current_user_id();
    }

    if (empty($user_id)) {
        return false;
    }

    $date_limite = (new DateTime('-7 days', wp_timezone()))->format('Y-m-d H:i:s');

    $orders = wc_get_orders(array(
        'customer_id'  => $user_id,
        'status'       => array('wc-completed', 'wc-processing'),
        'date_created' => '>' . $date_limite,
        'limit'        => -1,
        'return'       => 'objects',
    ));

    if (empty($orders)) {
        return false;
    }

    foreach ($orders as $order) {
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            if (has_term('futbol', 'product_cat', $product_id)) {
                return true;
            }
        }
    }

    return false;
}

function predictafseGetPartidoContexto($post_id = 0) {
    if (empty($post_id)) {
        $post_id = get_the_ID();
    }

    $contexto = array(
        'post_id'           => $post_id,
        'local'             => null,
        'visitante'         => null,
        'liga'              => null,
        'local_nombre'      => '',
        'visitante_nombre'  => '',
        'liga_nombre'       => '',
        'fecha'             => '',
        'hora'              => '',
        'ciudad'            => '',
        'estadio'           => '',
        'prob'              => predictafseGetPartidoProbabilities($post_id),
        'prediccion_api'    => array(),
        'analisis'          => array(),
        'marcador'          => '—',
        'recomendacion'     => '',
    );

    if (!function_exists('get_field')) {
        return $contexto;
    }

    $local = get_field('local_fixture', $post_id);
    $visitante = get_field('visitante_fixture', $post_id);
    $liga = get_field('liga_fixture', $post_id);

    if (is_object($local)) {
        $contexto['local'] = $local;
        $contexto['local_nombre'] = get_the_title($local->ID);
    }

    if (is_object($visitante)) {
        $contexto['visitante'] = $visitante;
        $contexto['visitante_nombre'] = get_the_title($visitante->ID);
    }

    if (is_object($liga)) {
        $contexto['liga'] = $liga;
        $contexto['liga_nombre'] = $liga->post_title;
    }

    $contexto['fecha'] = (string) (get_field('fecha_fixture', $post_id) ?? '');
    $contexto['hora'] = (string) (get_field('hora_fixture', $post_id) ?? '');
    $contexto['ciudad'] = (string) (get_field('ciudad_fixture', $post_id) ?? '');
    $contexto['estadio'] = (string) (get_field('estadio_fixture', $post_id) ?? '');

    $json_api = get_field('json_prediccion_api', $post_id);
    if (!empty($json_api)) {
        $data = json_decode($json_api, true);
        if (is_array($data)) {
            $contexto['prediccion_api'] = $data;
            if (!empty($data['goals']['home']) && !empty($data['goals']['away'])) {
                $contexto['marcador'] = $data['goals']['home'] . ' - ' . $data['goals']['away'];
            }
            if (!empty($data['advice'])) {
                $contexto['recomendacion'] = $data['advice'];
            }
        }
    }

    $analisis_json = get_field('analisis_fixture', $post_id);
    if (!empty($analisis_json)) {
        $data = json_decode($analisis_json, true);
        if (is_array($data)) {
            $contexto['analisis'] = $data;
            if ($contexto['recomendacion'] === '' && !empty($data['possible_winner']['value'])) {
                $contexto['recomendacion'] = $data['possible_winner']['value'];
            }
        }
    }

    return $contexto;
}

function predictafseUserCanViewPartidoPremium($post_id = 0) {
    if (empty($post_id)) {
        $post_id = get_the_ID();
    }

    if (current_user_can('administrator')) {
        return true;
    }

    if (!is_user_logged_in()) {
        return false;
    }

    if (predictafseUserHasRecentFutbolOrders(get_current_user_id())) {
        return true;
    }

    if (!function_exists('get_field')) {
        return false;
    }

    $user_id = get_current_user_id();
    $prueba_usada = get_field('user_prueba_usada', 'user_' . $user_id);
    $num_vistos = (int) (get_field('user_num_vistos', 'user_' . $user_id) ?? 0);

    if ($prueba_usada && $num_vistos >= 1) {
        return false;
    }

    if (!$prueba_usada && $num_vistos < 1) {
        update_field('user_num_vistos', 1, 'user_' . $user_id);
        update_field('user_prueba_usada', 1, 'user_' . $user_id);
        return true;
    }

    return false;
}

function predictafseRenderPartidoMetricas($contexto) {
    $prob = $contexto['prob'];
    $local_short = predictafseGetTeamShortName($contexto['local']);
    $visitante_short = predictafseGetTeamShortName($contexto['visitante']);
    $home = is_numeric($prob['home']) ? (int) $prob['home'] : 0;
    $draw = is_numeric($prob['draw']) ? (int) $prob['draw'] : 0;
    $away = is_numeric($prob['away']) ? (int) $prob['away'] : 0;

    ob_start();
    ?>
    <div class="partido-metricas">
        <div class="partido-metricas-head">
            <div>
                <span class="partido-metricas-label"><?php esc_html_e('Predictores en IA avanzada', 'predictafse'); ?></span>
                <h3><?php esc_html_e('Probabilidad de resultado', 'predictafse'); ?></h3>
            </div>
            <span class="partido-metricas-badge"><?php esc_html_e('Modelo V2.6-Neural', 'predictafse'); ?></span>
        </div>

        <?php if ($home > 0 || $draw > 0 || $away > 0) : ?>
            <div class="partido-metricas-bar">
                <?php if ($home > 0) : ?>
                    <span class="partido-metricas-seg home" style="width:<?php echo esc_attr($home); ?>%"><?php echo ($home >= 15) ? esc_html($home . '%') : ''; ?></span>
                <?php endif; ?>
                <?php if ($draw > 0) : ?>
                    <span class="partido-metricas-seg draw" style="width:<?php echo esc_attr($draw); ?>%"><?php echo ($draw >= 15) ? esc_html($draw . '%') : ''; ?></span>
                <?php endif; ?>
                <?php if ($away > 0) : ?>
                    <span class="partido-metricas-seg away" style="width:<?php echo esc_attr($away); ?>%"><?php echo ($away >= 15) ? esc_html($away . '%') : ''; ?></span>
                <?php endif; ?>
            </div>

            <div class="partido-metricas-grid">
                <div class="partido-metricas-item">
                    <span><?php echo esc_html(sprintf(__('Victoria %s', 'predictafse'), $local_short)); ?></span>
                    <strong class="home"><?php echo esc_html($prob['home']); ?>%</strong>
                </div>
                <div class="partido-metricas-item">
                    <span><?php esc_html_e('Empate', 'predictafse'); ?></span>
                    <strong class="draw"><?php echo esc_html($prob['draw']); ?>%</strong>
                </div>
                <div class="partido-metricas-item">
                    <span><?php echo esc_html(sprintf(__('Victoria %s', 'predictafse'), $visitante_short)); ?></span>
                    <strong class="away"><?php echo esc_html($prob['away']); ?>%</strong>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($contexto['recomendacion'] !== '') : ?>
            <div class="partido-metricas-tip">
                <strong><?php esc_html_e('Recomendación sugerida:', 'predictafse'); ?></strong>
                <span><?php echo esc_html($contexto['recomendacion']); ?></span>
                <?php if ($contexto['marcador'] !== '—') : ?>
                    <p><?php echo esc_html(sprintf(__('Marcador simulado: %s', 'predictafse'), $contexto['marcador'])); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

function partidoHero() {
    if (!is_singular('partido')) {
        return '';
    }

    $contexto = predictafseGetPartidoContexto();
    $home_url = esc_url(home_url('/'));

    ob_start();
    ?>
    <nav class="partido-breadcrumb" aria-label="<?php esc_attr_e('Ruta de navegación', 'predictafse'); ?>">
        <a href="<?php echo $home_url; ?>"><?php esc_html_e('Home', 'predictafse'); ?></a>
        <?php if ($contexto['liga_nombre'] !== '') : ?>
            <span class="partido-breadcrumb-sep">›</span>
            <span><?php echo esc_html($contexto['liga_nombre']); ?></span>
        <?php endif; ?>
        <?php if ($contexto['local_nombre'] !== '' && $contexto['visitante_nombre'] !== '') : ?>
            <span class="partido-breadcrumb-sep">›</span>
            <span class="partido-breadcrumb-current"><?php echo esc_html($contexto['local_nombre'] . ' vs ' . $contexto['visitante_nombre']); ?></span>
        <?php endif; ?>
    </nav>

    <header class="partido-hero">
        <div class="partido-hero-equipos">
            <?php if ($contexto['local_nombre'] !== '') : ?>
                <div class="partido-hero-team local">
                    <span class="partido-hero-role"><?php esc_html_e('Local', 'predictafse'); ?></span>
                    <h2 class="partido-hero-name"><?php echo esc_html($contexto['local_nombre']); ?></h2>
                </div>
            <?php endif; ?>

            <div class="partido-hero-score">
                <span class="partido-hero-badge"><?php esc_html_e('Pronóstico de marcador IA', 'predictafse'); ?></span>
                <p class="partido-hero-marcador"><?php echo esc_html($contexto['marcador']); ?></p>
                <span class="partido-hero-score-note"><?php esc_html_e('Probabilidad de marcador correcto', 'predictafse'); ?></span>
            </div>

            <?php if ($contexto['visitante_nombre'] !== '') : ?>
                <div class="partido-hero-team away">
                    <span class="partido-hero-role"><?php esc_html_e('Visita', 'predictafse'); ?></span>
                    <h2 class="partido-hero-name"><?php echo esc_html($contexto['visitante_nombre']); ?></h2>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($contexto['recomendacion'] !== '') : ?>
            <div class="partido-hero-foot">
                <div>
                    <span class="partido-hero-foot-label"><?php esc_html_e('Predicción de algoritmo:', 'predictafse'); ?></span>
                    <p><?php echo esc_html($contexto['recomendacion']); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </header>
    <?php
    return ob_get_clean();
}
add_shortcode('partido_hero', 'partidoHero');

function partidoSidebar() {
    if (!is_singular('partido')) {
        return '';
    }

    ob_start();

    if (is_active_sidebar('sidebar-1')) {
        echo '<aside class="partido-sidebar-widgets">';
        dynamic_sidebar('sidebar-1');
        echo '</aside>';
    } else {
        echo partidoWidgetLateral();
    }

    return ob_get_clean();
}
add_shortcode('partido_sidebar', 'partidoSidebar');

function partidoWidgetLateral() {
    if (!is_singular('partido')) {
        return '';
    }

    $contexto = predictafseGetPartidoContexto();
    $membresia_url = esc_url(home_url('/membresia/'));

    ob_start();
    ?>
    <aside class="partido-sidebar">
        <div class="partido-widget partido-datos">
            <h3><?php esc_html_e('Datos del partido', 'predictafse'); ?></h3>
            <ul>
                <?php if ($contexto['local_nombre'] !== '') : ?>
                    <li><strong><?php esc_html_e('Equipo local:', 'predictafse'); ?></strong> <?php echo esc_html($contexto['local_nombre']); ?></li>
                <?php endif; ?>
                <?php if ($contexto['visitante_nombre'] !== '') : ?>
                    <li><strong><?php esc_html_e('Equipo visitante:', 'predictafse'); ?></strong> <?php echo esc_html($contexto['visitante_nombre']); ?></li>
                <?php endif; ?>
                <?php if ($contexto['fecha'] !== '' || $contexto['hora'] !== '') : ?>
                    <li><strong><?php esc_html_e('Fecha y hora:', 'predictafse'); ?></strong> <?php echo esc_html(trim($contexto['fecha'] . ' ' . $contexto['hora'])); ?></li>
                <?php endif; ?>
                <?php if ($contexto['ciudad'] !== '') : ?>
                    <li><strong><?php esc_html_e('Ciudad:', 'predictafse'); ?></strong> <?php echo esc_html($contexto['ciudad']); ?></li>
                <?php endif; ?>
                <?php if ($contexto['liga_nombre'] !== '') : ?>
                    <li><strong><?php esc_html_e('Liga:', 'predictafse'); ?></strong> <?php echo esc_html($contexto['liga_nombre']); ?></li>
                <?php endif; ?>
                <?php if ($contexto['estadio'] !== '') : ?>
                    <li><strong><?php esc_html_e('Estadio:', 'predictafse'); ?></strong> <?php echo esc_html($contexto['estadio']); ?></li>
                <?php endif; ?>
            </ul>
        </div>

        <?php echo predictafseRenderPartidoMetricas($contexto); ?>

        <div class="partido-widget partido-cta-premium">
            <span class="partido-cta-badge"><?php esc_html_e('Módulo de suscripción', 'predictafse'); ?></span>
            <h4><?php esc_html_e('¿Deseas datos de las grandes ligas?', 'predictafse'); ?></h4>
            <p><?php esc_html_e('Únete a analistas y aficionados que reciben informes detallados cada jornada.', 'predictafse'); ?></p>
            <a class="partido-cta-btn" href="<?php echo $membresia_url; ?>"><?php esc_html_e('Registrarse en membresía premium', 'predictafse'); ?></a>
        </div>
    </aside>
    <?php
    return ob_get_clean();
}
add_shortcode('partido_widget_lateral', 'partidoWidgetLateral');

function partidoPremium() {
    if (!is_singular('partido')) {
        return '';
    }

    $post_id = get_the_ID();
    $contexto = predictafseGetPartidoContexto($post_id);
    $membresia_url = esc_url(home_url('/membresia/'));
    $login_url = function_exists('wc_get_page_permalink') ? esc_url(wc_get_page_permalink('myaccount')) : esc_url(wp_login_url());

    ob_start();
    ?>
    <section class="partido-premium">
        <h2><?php esc_html_e('Análisis premium del partido', 'predictafse'); ?></h2>

        <?php if (!is_user_logged_in()) : ?>
            <div class="partido-premium-lock">
                <p><?php esc_html_e('Inicia sesión para acceder al desglose táctico premium generado por IA.', 'predictafse'); ?></p>
                <a class="partido-cta-btn" href="<?php echo $login_url; ?>"><?php esc_html_e('Iniciar sesión', 'predictafse'); ?></a>
            </div>
        <?php elseif (!predictafseUserCanViewPartidoPremium($post_id)) : ?>
            <div class="partido-premium-lock">
                <p><?php esc_html_e('Has alcanzado el límite de pronósticos gratuitos. Activa tu membresía para ver el análisis completo.', 'predictafse'); ?></p>
                <a class="partido-cta-btn" href="<?php echo $membresia_url; ?>"><?php esc_html_e('Comprar membresía', 'predictafse'); ?></a>
            </div>
        <?php elseif (empty($contexto['analisis'])) : ?>
            <p class="partido-premium-empty"><?php esc_html_e('No hay datos premium disponibles para este partido.', 'predictafse'); ?></p>
        <?php else : ?>
            <ul class="partido-premium-list">
                <?php
                $items = array(
                    'possible_winner'    => __('Posible ganador', 'predictafse'),
                    'total_goals'        => __('Total de goles', 'predictafse'),
                    'both_teams_score'   => __('Ambos equipos marcan', 'predictafse'),
                    'total_corners'      => __('Total de corners', 'predictafse'),
                    'double_chance'      => __('Doble oportunidad', 'predictafse'),
                    'total_cards'        => __('Total de tarjetas', 'predictafse'),
                    'team_total_shots'   => __('Tiros al arco', 'predictafse'),
                    'asian_handicap'     => __('Handicap asiático', 'predictafse'),
                );

                foreach ($items as $key => $label) {
                    if (empty($contexto['analisis'][$key]['value'])) {
                        continue;
                    }
                    $valor = $contexto['analisis'][$key]['value'];
                    $prob = $contexto['analisis'][$key]['probability'] ?? '';
                    ?>
                    <li>
                        <strong><?php echo esc_html($label); ?>:</strong>
                        <?php echo esc_html($valor); ?>
                        <?php if ($prob !== '') : ?>
                            <span class="partido-premium-prob"><?php echo esc_html($prob); ?>%</span>
                        <?php endif; ?>
                    </li>
                    <?php
                }
                ?>
            </ul>

            <?php if (!empty($contexto['prediccion_api']['advice'])) : ?>
                <div class="partido-premium-advice">
                    <strong><?php esc_html_e('Recomendación IA:', 'predictafse'); ?></strong>
                    <p><?php echo esc_html($contexto['prediccion_api']['advice']); ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode('partido_premium', 'partidoPremium');

function predictafseRegistrarTagsPartido() {
    register_taxonomy_for_object_type('post_tag', 'partido');
}
add_action('init', 'predictafseRegistrarTagsPartido', 20);

function predictafseBodyClassPartido($classes) {
    if (is_singular('partido')) {
        $classes[] = 'predictafse-partido';
    }
    return $classes;
}
add_filter('body_class', 'predictafseBodyClassPartido');
