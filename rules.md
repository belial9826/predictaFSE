# Reglas del proyecto — Predicta FSE

Guía corta para mantener código consistente en el theme FSE de Pronósticos Para Hoy.

---

## 1. Referencia de diseño (Google AI Studio)

**Fuente de verdad visual:** `/home/belial9826/Proyectos/PronosticosParaHoy/design`

- Prototipo generado en [Google AI Studio](https://ai.studio/apps/d678cfb6-6c19-4e97-9227-8e41a22ad530).
- Antes de implementar una vista nueva, revisar el componente equivalente en `design/src/components/`.
- **No copiar React, Tailwind ni TypeScript al theme.** Traducir el diseño a:
  - Bloques FSE (`templates/`, `parts/`, `patterns/`)
  - Tokens de `theme.json`
  - SCSS en `source/scss/`
- Colores, tipografías y espaciados deben coincidir con `theme.json` y `_variables.scss` (no hardcodear valores distintos).
- Si el diseño introduce un color o tamaño nuevo, agregarlo primero en `theme.json` y `_variables.scss`.
- Pixel perfect basado en el diseño
- El usuario debe de editar todo el contenido desde el backend basado en el theme
- No usar clases en exceso en los elementos, usar pocas y con esta generar los estilos. Evita clases largas tipo: nombre_de_la_clase__*
- Transiciones suaves, scroll suave
- Usar skills de UX/UI disponibles

---

## 2. Estructura del theme

```
predictaFSE/
├── inc/           → PHP modular (shortcodes, enqueue, theme)
├── parts/         → Template parts FSE (header, footer…)
├── templates/     → Templates FSE (page, single, home…)
├── patterns/      → Patrones de bloques
├── source/scss/   → Estilos fuente (editar aquí)
├── source/js/     → Scripts fuente (editar aquí)
├── assets/        → CSS/JS compilados (no editar a mano)
├── theme.json     → Design tokens FSE
└── functions.php  → Setup + carga de inc/
```

---

## 3. PHP (procedural, camelCase)

- **Solo PHP procedural.** Sin clases, traits ni namespaces.
- Funciones en **camelCase**: `userHeader()`, `predictafseEnqueueAssets()`.
- Un archivo = una responsabilidad en `inc/`. Carga automática vía `functions.php`.
- Shortcodes: HTML con `ob_start()` / `ob_get_clean()` y `return`.
- Validar antes de usar: `isset()`, `empty()`, operador `??`.
- Escapar siempre: `esc_html()`, `esc_url()`, `esc_attr()`.
- Text domain: `predictafse`.
- Usar hooks nativos: `add_action`, `add_filter`, `add_shortcode`.

```php
function userHeader() {
    ob_start();
    ?>
    <div id="wrap-userHeader">...</div>
    <?php
    return ob_get_clean();
}
add_shortcode('user_header', 'userHeader');
```

---

## 4. Templates FSE (HTML de bloques)

- Archivos en `templates/` y `parts/` con comentarios de bloque WordPress.
- Etiquetas semánticas con `tagName`: `header`, `main`, `footer`, `article`.
- Estilos base con presets de `theme.json`: `var:preset|spacing|60`.
- Clases custom con `className` descriptivas: `FSE-header`, `site-main-page`, `page-header-container`.
- Lógica dinámica vía shortcodes dentro de `<!-- wp:shortcode -->`.
- Partes reutilizables con `<!-- wp:template-part {"slug":"header"} /-->`.

```html
<!-- wp:group {"tagName":"main","className":"site-main-page","layout":{"type":"constrained"}} -->
<main class="wp-block-group site-main-page">
    <!-- wp:post-content {"className":"entry-content"} /-->
</main>
<!-- /wp:group -->
```

---

## 5. SCSS

- Editar solo en `source/scss/`. Compilar con `npx gulp`.
- Entrada principal: `main.scss` con `@use` de partials.
- Variables globales en `_variables.scss` (espejo de `theme.json`).
- Un partial por sección: `_header.scss`, `_components.scss`, etc.
- Usar módulo Sass moderno: `@use 'sass:color'` en lugar de `lighten()` / `darken()`.
- **Nomenclatura de clases:**
  - Contenedores: kebab-case → `.wrap-userlogin`, `.page-header-container`
  - Prefijo de bloque FSE cuando aplique → `.FSE-header`
  - Estados → `.open`, `.is-open`, `.has-plan`
  - IDs solo para anclas JS puntuales → `#wrap-userHeader`
- Anidar con moderación (máximo 3 niveles).
- Comentarios solo para lógica no obvia (ej. puentes hover, animaciones).

---

## 6. JavaScript (jQuery)

- Editar solo `source/js/main.js`. Compilar con `npx gulp`.
- **Solo jQuery.** Sin frameworks modernos ni `addEventListener`.
- Envolver en `jQuery(document).ready(function($) { ... })`.
- Delegación de eventos: `$(document).on('click', '.selector', ...)`.
- Variables jQuery con prefijo `$`: `const $drawer = $('#mobile-nav-drawer')`.
- Estados con clases CSS (`.open`, `.is-open`), no estilos inline.
- Comentarios breves en español por bloque de comportamiento.

```javascript
$(document).on('mouseenter', '.wrap-userlogin.has-plan .wrap-user-actions', function() {
    $(this).find('.menu-user').addClass('open');
});
```

---

## 7. Design tokens (`theme.json`)

| Token | Uso |
|-------|-----|
| `primary` (#10b981) | Verde IA, CTAs, acentos |
| `secondary` (#3b82f6) | Azul métricas |
| `accent` (#6cabdd) | Enlaces, equipos |
| `gold` (#fbbf24) | Planes VIP |
| `base` (#050505) | Fondo general |
| `sans` / `mono` | Plus Jakarta Sans / JetBrains Mono |

Preferir presets del editor antes de valores sueltos en bloques.

---

## 8. Build y flujo de trabajo

```bash
cd www/wp-content/themes/predictaFSE
npm install          # primera vez
npx gulp             # compila + watch
npx gulp styles      # solo CSS
npx gulp scripts     # solo JS
```

1. Consultar diseño en `/design`.
2. Implementar en templates/parts/patterns + SCSS/JS.
3. Compilar con Gulp.
4. Verificar en front y en el editor de sitio.

---

## 9. Principios generales

- Código **corto, legible y directo**. Sin abstracciones innecesarias.
- Reutilizar lo existente antes de crear archivos nuevos.
- No refactorizar a POO ni introducir dependencias no usadas en el theme.
- Cambios mínimos y enfocados: resolver solo lo pedido.
- HTML semántico. CSS plano. JS con jQuery delegado.
