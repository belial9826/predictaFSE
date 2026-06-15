<?php

function predictafse_partido_prob($post_id) {
    $result = array(
        'home'       => '—',
        'draw'       => '—',
        'away'       => '—',
        'confidence' => '—',
    );

    if (!function_exists('get_field')) {
        return $result;
    }

    $json_api = get_field('json_prediccion_api', $post_id);
    if (!empty($json_api)) {
        $data = json_decode($json_api, true);
        if (is_array($data) && !empty($data['percent']) && is_array($data['percent'])) {
            $result['home'] = predictafse_pct_clean($data['percent']['home'] ?? '');
            $result['draw'] = predictafse_pct_clean($data['percent']['draw'] ?? '');
            $result['away'] = predictafse_pct_clean($data['percent']['away'] ?? '');
        }
    }

    $analisis = get_field('analisis_fixture', $post_id);
    if (!empty($analisis)) {
        $data = json_decode($analisis, true);
        if (is_array($data) && !empty($data['possible_winner']['probability'])) {
            $result['confidence'] = predictafse_pct_clean($data['possible_winner']['probability']);
        }
    }

    return $result;
}

function predictafse_pct_clean($value) {
    if ($value === '' || $value === null) {
        return '—';
    }
    return str_replace('%', '', (string) $value);
}

function predictafse_team_short($team_post) {
    if (empty($team_post) || !is_object($team_post)) {
        return '—';
    }
    $title = get_the_title($team_post->ID);
    if ($title === '') {
        return '—';
    }
    return mb_strtoupper(mb_substr($title, 0, 3));
}

function predictafse_partidos_url() {
    $pagina = get_page_by_path('pronosticos-de-futbol');
    if ($pagina instanceof WP_Post) {
        $url = get_permalink($pagina->ID);
        if (!empty($url)) {
            return $url;
        }
    }

    $link = get_post_type_archive_link('partido');
    if (!empty($link)) {
        return $link;
    }

    $posts_page_id = (int) get_option('page_for_posts');
    if ($posts_page_id > 0) {
        $posts_url = get_permalink($posts_page_id);
        if (!empty($posts_url)) {
            return $posts_url;
        }
    }

    return home_url('/pronosticos-de-futbol/');
}

function predictafse_partido_forma($data, $side) {
    if (!is_array($data) || empty($side)) {
        return '';
    }

    $paths = array(
        array('teams', $side, 'last_5', 'form'),
        array('teams', $side, 'league', 'form'),
        array('comparison', $side, 'form'),
    );

    foreach ($paths as $path) {
        $value = $data;
        foreach ($path as $key) {
            if (!is_array($value) || !isset($value[$key])) {
                $value = null;
                break;
            }
            $value = $value[$key];
        }
        if (is_string($value) && $value !== '') {
            return preg_replace('/[^WDL]/i', '', strtoupper($value));
        }
    }

    return '';
}

function predictafse_partido_excerpt($post_id, $contexto) {
    $excerpt = get_the_excerpt($post_id);
    if (!empty($excerpt)) {
        return wp_trim_words(wp_strip_all_tags($excerpt), 42, '…');
    }

    if (!empty($contexto['recomendacion'])) {
        return wp_trim_words(wp_strip_all_tags($contexto['recomendacion']), 42, '…');
    }

    if (!empty($contexto['analisis']['comparison'])) {
        $comparison = $contexto['analisis']['comparison'];
        if (is_string($comparison)) {
            return wp_trim_words(wp_strip_all_tags($comparison), 42, '…');
        }
        if (is_array($comparison)) {
            foreach ($comparison as $texto) {
                if (is_string($texto) && $texto !== '') {
                    return wp_trim_words(wp_strip_all_tags($texto), 42, '…');
                }
            }
        }
    }

    $content = get_post_field('post_content', $post_id);
    if (!empty($content)) {
        return wp_trim_words(wp_strip_all_tags($content), 42, '…');
    }

    return '';
}

function predictafse_partido_date($fecha, $hora) {
    $fecha = trim((string) $fecha);
    $hora = trim((string) $hora);

    if ($fecha === '') {
        return '';
    }

    if ($hora === '') {
        $hora = '00:00';
    }

    if (preg_match('/^\d{1,2}:\d{2}/', $hora) !== 1) {
        $hora = '00:00';
    }

    $formatos = array('Y-m-d', 'd/m/Y', 'm/d/y', 'd-m-Y', 'm/d/Y');
    foreach ($formatos as $formato) {
        $dt = DateTime::createFromFormat($formato, $fecha);
        if ($dt instanceof DateTime) {
            return $dt->format('Y-m-d') . 'T' . $hora . ':00';
        }
    }

    return $fecha . 'T' . $hora . ':00';
}

function predictafse_partido_card_data($post_id) {
    $contexto = predictafse_partido_ctx($post_id);

    $local_forma = '';
    $visitante_forma = '';
    if (!empty($contexto['prediccion_api'])) {
        $local_forma = predictafse_partido_forma($contexto['prediccion_api'], 'home');
        $visitante_forma = predictafse_partido_forma($contexto['prediccion_api'], 'away');
    }

    $local_nombre = $contexto['local_nombre'];
    $visitante_nombre = $contexto['visitante_nombre'];
    $liga_nombre = $contexto['liga_nombre'];
    $excerpt = predictafse_partido_excerpt($post_id, $contexto);

    return array(
        'post_id'          => $post_id,
        'liga'             => $liga_nombre,
        'local_nombre'     => $local_nombre,
        'visitante_nombre' => $visitante_nombre,
        'local_forma'      => $local_forma,
        'visitante_forma'  => $visitante_forma,
        'local_short'      => predictafse_team_short($contexto['local']),
        'visitante_short'  => predictafse_team_short($contexto['visitante']),
        'excerpt'          => $excerpt,
        'prob'             => $contexto['prob'],
        'url'              => get_permalink($post_id),
        'fecha'            => $contexto['fecha'],
        'hora'             => $contexto['hora'],
        'ciudad'           => $contexto['ciudad'],
        'estadio'          => $contexto['estadio'],
        'start_date'       => predictafse_partido_date($contexto['fecha'], $contexto['hora']),
        'event_name'       => ($local_nombre !== '' && $visitante_nombre !== '')
            ? $local_nombre . ' vs ' . $visitante_nombre
            : get_the_title($post_id),
    );
}

function predictafse_partido_pred_props($prob) {
    $props = array();

    if (!empty($prob['home']) && $prob['home'] !== '—') {
        $props[] = array(
            '@type' => 'PropertyValue',
            'name'  => 'homeWinProbability',
            'value' => $prob['home'] . '%',
        );
    }

    if (!empty($prob['draw']) && $prob['draw'] !== '—') {
        $props[] = array(
            '@type' => 'PropertyValue',
            'name'  => 'drawProbability',
            'value' => $prob['draw'] . '%',
        );
    }

    if (!empty($prob['away']) && $prob['away'] !== '—') {
        $props[] = array(
            '@type' => 'PropertyValue',
            'name'  => 'awayWinProbability',
            'value' => $prob['away'] . '%',
        );
    }

    if (!empty($prob['confidence']) && $prob['confidence'] !== '—') {
        $props[] = array(
            '@type' => 'PropertyValue',
            'name'  => 'aiConfidenceScore',
            'value' => $prob['confidence'] . '%',
        );
    }

    return $props;
}

function predictafse_partido_schema_event($card) {
    $event_name = $card['event_name'] ?? '';
    $liga = $card['liga'] ?? '';
    $local = $card['local_nombre'] ?? '';
    $visitante = $card['visitante_nombre'] ?? '';
    $url = $card['url'] ?? '';
    $excerpt = $card['excerpt'] ?? '';

    if ($event_name === '') {
        return '';
    }

    $descripcion = $excerpt;
    if ($descripcion === '' && $liga !== '') {
        $descripcion = sprintf(
            'Análisis táctico y pronósticos IA para %s (%s).',
            $event_name,
            $liga
        );
    } elseif ($descripcion === '') {
        $descripcion = 'Análisis táctico y pronósticos IA para ' . $event_name . '.';
    }

    $schema = array(
        '@context'            => 'https://schema.org',
        '@type'               => 'SportsEvent',
        'name'                => $event_name . ' - Análisis Predictivo IA',
        'description'         => $descripcion,
        'sport'               => 'Soccer',
        'eventStatus'         => 'https://schema.org/EventScheduled',
        'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
        'url'                 => $url,
        'homeTeam'            => array(
            '@type' => 'SportsTeam',
            'name'  => $local,
        ),
        'awayTeam'            => array(
            '@type' => 'SportsTeam',
            'name'  => $visitante,
        ),
        'competitor'          => array(
            array('@type' => 'SportsTeam', 'name' => $local),
            array('@type' => 'SportsTeam', 'name' => $visitante),
        ),
    );

    if (!empty($card['start_date'])) {
        $schema['startDate'] = $card['start_date'];
    }

    if ($liga !== '') {
        $schema['competition'] = $liga;
    }

    if (!empty($card['estadio']) || !empty($card['ciudad'])) {
        $schema['location'] = array(
            '@type' => 'Place',
            'name'  => $card['estadio'] ?? $card['ciudad'],
        );
        if (!empty($card['ciudad'])) {
            $schema['location']['address'] = $card['ciudad'];
        }
    }

    if ($url !== '') {
        $schema['offers'] = array(
            '@type'         => 'Offer',
            'url'           => $url,
            'price'         => '0.00',
            'priceCurrency' => 'USD',
            'availability'  => 'https://schema.org/InStock',
        );
    }

    $prediction_props = predictafse_partido_pred_props($card['prob'] ?? array());
    if (!empty($prediction_props)) {
        $schema['additionalProperty'] = $prediction_props;
    }

    return wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function predictafse_partidos_schema_list($cards) {
    if (empty($cards)) {
        return '';
    }

    $items = array();
    $pos = 1;

    foreach ($cards as $card) {
        if (empty($card['url'])) {
            continue;
        }
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $pos,
            'url'      => $card['url'],
            'name'     => $card['event_name'] ?? '',
        );
        $pos++;
    }

    if (empty($items)) {
        return '';
    }

    $schema = array(
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => 'Partidos Destacados & Pronósticos IA',
        'itemListElement' => $items,
    );

    return wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function predictafse_partido_prob_html($value) {
    if ($value === '—' || $value === '') {
        return '—';
    }
    return esc_html($value) . '%';
}

function predictafse_partidos_more_btn($url) {
    if ($url === '') {
        return '';
    }

    $texto = esc_html__('Ver todos los partidos', 'predictafse');

    return '<div class="home-partidos-more"><a class="home-partidos-more-btn" href="' . esc_url($url) . '"><span class="home-partidos-more-btn-inner">' . $texto . '<span class="home-partidos-more-btn-icon" aria-hidden="true"></span></span><span class="home-partidos-more-btn-shine" aria-hidden="true"></span></a></div>';
}

function predictafse_partido_card_html($card) {
    $liga = $card['liga'] ?? '';
    $local_nombre = $card['local_nombre'] ?? '';
    $visitante_nombre = $card['visitante_nombre'] ?? '';
    $local_forma = $card['local_forma'] ?? '';
    $visitante_forma = $card['visitante_forma'] ?? '';
    $local_short = $card['local_short'] ?? '—';
    $visitante_short = $card['visitante_short'] ?? '—';
    $excerpt = $card['excerpt'] ?? '';
    $prob = $card['prob'] ?? array();
    $url = $card['url'] ?? '';
    $confidence = $prob['confidence'] ?? '—';
    $event_name = $card['event_name'] ?? '';
    $schema_json = predictafse_partido_schema_event($card);
    ?>
    <article
        class="partido-card"
        itemscope
        itemtype="https://schema.org/SportsEvent"
        <?php echo !empty($card['post_id']) ? ' id="featured-match-card-' . esc_attr($card['post_id']) . '"' : ''; ?>
    >
        <?php if ($schema_json !== '') : ?>
            <script type="application/ld+json"><?php echo $schema_json; ?></script>
        <?php endif; ?>

        <span class="screen-reader-text" itemprop="name"><?php echo esc_html($event_name); ?></span>
        <?php if (!empty($card['start_date'])) : ?>
            <meta itemprop="startDate" content="<?php echo esc_attr($card['start_date']); ?>">
        <?php endif; ?>
        <?php if ($url !== '') : ?>
            <link itemprop="url" href="<?php echo esc_url($url); ?>">
        <?php endif; ?>
        <?php if ($liga !== '') : ?>
            <meta itemprop="competition" content="<?php echo esc_attr($liga); ?>">
        <?php endif; ?>

        <div class="partido-card-glow" aria-hidden="true"></div>

        <div class="partido-card-body">
            <div class="partido-card-head">
                <?php if ($liga !== '') : ?>
                    <span class="partido-liga"><?php echo esc_html($liga); ?></span>
                <?php endif; ?>
                <span class="partido-confianza" <?php echo ($confidence !== '—' && $confidence !== '') ? ' itemprop="additionalProperty" itemscope itemtype="https://schema.org/PropertyValue"' : ''; ?>>
                    <?php if ($confidence !== '—' && $confidence !== '') : ?>
                        <meta itemprop="name" content="aiConfidenceScore">
                        <span itemprop="value"><?php echo esc_html__('Confianza:', 'predictafse') . ' ' . esc_html($confidence) . '%'; ?></span>
                    <?php else : ?>
                        <?php esc_html_e('Pronóstico IA', 'predictafse'); ?>
                    <?php endif; ?>
                </span>
            </div>

            <div class="partido-equipos">
                <?php if ($local_nombre !== '') : ?>
                    <div class="partido-equipo" itemprop="homeTeam" itemscope itemtype="https://schema.org/SportsTeam">
                        <span class="partido-nombre" itemprop="name"><?php echo esc_html($local_nombre); ?></span>
                        <?php if ($local_forma !== '') : ?>
                            <span class="partido-forma" itemprop="additionalProperty" itemscope itemtype="https://schema.org/PropertyValue">
                                <meta itemprop="name" content="recentForm">
                                (<span itemprop="value"><?php echo esc_html($local_forma); ?></span>)
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php if ($visitante_nombre !== '') : ?>
                    <div class="partido-equipo" itemprop="awayTeam" itemscope itemtype="https://schema.org/SportsTeam">
                        <span class="partido-nombre" itemprop="name"><?php echo esc_html($visitante_nombre); ?></span>
                        <?php if ($visitante_forma !== '') : ?>
                            <span class="partido-forma" itemprop="additionalProperty" itemscope itemtype="https://schema.org/PropertyValue">
                                <meta itemprop="name" content="recentForm">
                                (<span itemprop="value"><?php echo esc_html($visitante_forma); ?></span>)
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($excerpt !== '') : ?>
                <p class="partido-excerpt" itemprop="description"><?php echo esc_html($excerpt); ?></p>
            <?php endif; ?>

            <div class="partido-proyeccion">
                <span class="partido-proy-label"><?php esc_html_e('PROYECCIÓN VICTORIA:', 'predictafse'); ?></span>
                <div class="partido-prob">
                    <div class="partido-prob-cell">
                        <span class="partido-prob-team"><?php echo esc_html($local_short); ?></span>
                        <div class="partido-prob-box home" itemprop="additionalProperty" itemscope itemtype="https://schema.org/PropertyValue">
                            <meta itemprop="name" content="homeWinProbability">
                            <span class="partido-prob-val home" itemprop="value"><?php echo predictafse_partido_prob_html($prob['home'] ?? '—'); ?></span>
                        </div>
                    </div>
                    <div class="partido-prob-cell">
                        <span class="partido-prob-team">X</span>
                        <div class="partido-prob-box draw" itemprop="additionalProperty" itemscope itemtype="https://schema.org/PropertyValue">
                            <meta itemprop="name" content="drawProbability">
                            <span class="partido-prob-val draw" itemprop="value"><?php echo predictafse_partido_prob_html($prob['draw'] ?? '—'); ?></span>
                        </div>
                    </div>
                    <div class="partido-prob-cell">
                        <span class="partido-prob-team"><?php echo esc_html($visitante_short); ?></span>
                        <div class="partido-prob-box away" itemprop="additionalProperty" itemscope itemtype="https://schema.org/PropertyValue">
                            <meta itemprop="name" content="awayWinProbability">
                            <span class="partido-prob-val away" itemprop="value"><?php echo predictafse_partido_prob_html($prob['away'] ?? '—'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($url !== '') : ?>
            <a class="partido-btn" href="<?php echo esc_url($url); ?>">
                <span class="partido-btn-text"><?php esc_html_e('Ver Análisis Táctico e IA', 'predictafse'); ?></span>
                <span class="partido-btn-icon" aria-hidden="true"></span>
            </a>
        <?php endif; ?>
    </article>
    <?php
}

function predictafse_sc_partidos($atts) {
    $atts = shortcode_atts(array(
        'per_page'  => 9,
        'home'      => '0',
        'show_more' => '',
    ), $atts, 'partidos_grid');

    $per_page = max(1, (int) $atts['per_page']);
    $es_home = ($atts['home'] === '1' || $atts['home'] === 'true');

    if ($es_home && $atts['show_more'] === '') {
        $atts['show_more'] = '1';
    }

    if ($es_home && (int) $atts['per_page'] > 6) {
        $per_page = 6;
    }

    $paged = get_query_var('paged');
    if (empty($paged)) {
        $paged = get_query_var('page');
    }
    $paged = max(1, (int) $paged);

    $query = new WP_Query(array(
        'post_type'              => 'partido',
        'post_status'            => 'publish',
        'posts_per_page'         => $per_page,
        'paged'                  => $paged,
        'meta_key'               => 'timestamp_fixture',
        'orderby'                => 'meta_value_num',
        'order'                  => 'DESC',
        'update_post_meta_cache' => true,
    ));

    $mostrar_mas = ($atts['show_more'] === '1' || $atts['show_more'] === 'true');
    $cards = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $cards[] = predictafse_partido_card_data(get_the_ID());
        }
        wp_reset_postdata();
    }

    $item_list_schema = predictafse_partidos_schema_list($cards);

    ob_start();

    if ($item_list_schema !== '') {
        echo '<script type="application/ld+json">' . $item_list_schema . '</script>';
    }

    if ($es_home) {
        echo '<div class="home-partidos-block">';
    } else {
        echo '<div class="partidos-directory-block">';
    }
    ?>
    <div class="partidos-grid<?php echo $es_home ? ' partidos-grid--home' : ''; ?>">
        <?php if (!empty($cards)) : ?>
            <?php foreach ($cards as $card) : ?>
                <?php predictafse_partido_card_html($card); ?>
            <?php endforeach; ?>
        <?php else : ?>
            <p class="partidos-empty"><?php esc_html_e('No hay partidos publicados en este momento.', 'predictafse'); ?></p>
        <?php endif; ?>
    </div>

    <?php if ($mostrar_mas && $es_home && $query->found_posts > 0) : ?>
        <?php echo predictafse_partidos_more_btn(predictafse_partidos_url()); ?>
    <?php endif; ?>

    <?php if ($query->max_num_pages > 1 && !$es_home) : ?>
        <nav class="partidos-pagination" aria-label="<?php esc_attr_e('Paginación de partidos', 'predictafse'); ?>">
            <?php
            echo paginate_links(array(
                'total'     => $query->max_num_pages,
                'current'   => $paged,
                'type'      => 'list',
                'mid_size'  => 1,
                'end_size'  => 1,
                'prev_text' => '&laquo; ' . esc_html__('Anterior', 'predictafse'),
                'next_text' => esc_html__('Siguiente', 'predictafse') . ' &raquo;',
            ));
            ?>
        </nav>
    <?php endif; ?>

    <?php
    if ($es_home) {
        echo '</div>';
    } else {
        echo '</div>';
    }

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('partidos_grid', 'predictafse_sc_partidos');
