---
trigger: always_on
---

# Reglas de Proyecto Cursor — Theme FSE Devengine Pro

## Objetivo general

Convertir un diseño existente en una ruta local hacia un **theme Full Site Editing (FSE) de WordPress**, manteniendo fidelidad visual, código limpio y arquitectura escalable.

---

## Contexto del proyecto

- **Diseño fuente:** `/home/belial9826/Proyectos/PronosticosParaHoy/design`
- **Theme (este repo):** `/home/belial9826/Proyectos/PronosticosParaHoy/www/wp-content/themes/predictaFSE`

---

## Reglas generales para Cursor

1. Responder y generar código en español.
2. Priorizar estándares de WordPress modernos.
3. Usar **Full Site Editing (FSE)**, no themes clásicos salvo petición explícita.
4. Mantener estructura limpia, reusable y orientada a producción.
5. No duplicar estilos innecesarios.
6. No copiar dependencias del proyecto fuente si no son necesarias.
7. Analizar primero el diseño fuente antes de crear archivos.
8. Clasificar componentes así:
   - **Patterns** — secciones reutilizables (hero, FAQ, CTA, cards de servicios, etc.).
   - **Template parts** — header, footer, avisos globales.

---

## Reglas de desarrollo (Cursor IDE)

**Alcance:** aplican al **código del theme** (`functions.php`, plantillas/patterns HTML, hojas de estilo compiladas en `assets/css/`, `assets/js/theme.js`). El JavaScript moderno (React, `@wordpress/scripts`) del plugin **devengine-pro-blocks** se rige por el **`.cursorrules` del plugin**, no por la sección de JS siguiente.

### Principios generales

- Responder siempre en **español**.
- Priorizar **simplicidad**, legibilidad y mantenibilidad.
- Evitar sobre-ingeniería y abstracciones innecesarias.
- Código entendible para desarrolladores de nivel intermedio.
- Mantener **consistencia** en estilo y estructura.
- Evitar **duplicación** de código.
- Reducir complejidad innecesaria.
- **Validar** correctamente entradas y salidas.

### PHP (compatible con PHP 8.3 y WordPress)

- PHP **procedural** exclusivamente (**no** POO en el theme).
- **No** utilizar clases, traits ni namespaces en el código del theme.
- Código **lineal**, explícito y fácil de seguir.
- Usar **funciones** solo cuando aporten claridad real.
- Evitar funciones auxiliares pequeñas o innecesarias.
- Evitar símbolos, acentos y caracteres especiales en nombres de **funciones PHP**; usar **camelCase** con prefijo del theme: `devengineProNombreFuncion()` (ej. `devengineProSetup()`). Así se cumple `nombreFuncion()` y se reduce colisión con el núcleo u otros plugins. Los **slugs expuestos en contenido** (p. ej. tag del shortcode `[devengine_pro_year]`) pueden mantener guiones bajos por compatibilidad con plantillas ya guardadas; lo importante es que las **funciones** en `functions.php` sigan camelCase.

#### Validación y seguridad

- Validar siempre la **existencia** de datos antes de usarlos.
- Usar `isset()` o `empty()` según el caso.
- Validar **tipos** antes de operar.
- Usar el operador de **fusión nula** (`??`) cuando aplique.

```php
$valor = $_POST['campo'] ?? '';
```

- **Sanitizar** entradas, especialmente en WordPress.

```php
$texto = sanitize_text_field( $_POST['texto'] ?? '' );
```

- **Escapar** siempre las salidas.

```php
echo esc_html( $texto );
```

#### Arreglos y datos

- Verificar existencia de **índices** antes de acceder.

```php
if ( ! empty( $array['clave'] ) ) {
	// lógica
}
```

- Evitar warnings y notices.
- No asumir la estructura de los datos sin validación previa.

#### WordPress

- Usar hooks estándar (`add_action`, `add_filter`).
- Usar funciones nativas de WordPress para **sanitización** y **escape**.
- Mantener compatibilidad con el ecosistema WordPress.
- Evitar **consultas directas** a la base de datos; preferir funciones nativas.

### HTML

- HTML **semántico** (`header`, `section`, `article`, `nav`, `main`, etc.).
- Evitar `div` sin propósito claro.
- No usar wrappers redundantes.
- Priorizar estructura clara sobre estilos complejos.
- Jerarquía lógica del DOM.

### JavaScript (jQuery obligatorio en el theme)

- En **`assets/js/theme.js`** (y cualquier JS del theme): usar **jQuery** exclusivamente.
- Manejar eventos con **delegación** (`$(document).on(...)`).
- Llamadas Ajax con `$.ajax({ ... })`.
- **No** usar `addEventListener`.
- **No** introducir frameworks modernos en el theme.
- Código directo, corto y fácil de mantener.
- Evitar funciones auxiliares innecesarias.
- Usar `let` o `const` en lugar de `var`.

```javascript
$( document ).on( 'click', '.clase', function () {
	// lógica
} );

$.ajax( {
	type: 'GET',
	url: url,
	dataType: 'jsonp',
	success: function ( respuesta ) {
		$( '#resultado' ).empty().append( respuesta );
	},
} );
```

### CSS y Preprocesador SASS

- **Estructura Modular SASS:** Los estilos del theme se desarrollan utilizando SASS en la carpeta `src/scss/` y se compilan automáticamente mediante Gulp (`npx gulp`) a la carpeta `assets/css/` en hojas minificadas dinámicas.
- **Hojas de Estilo Específicas por Vista:**
  - `src/scss/global.scss` ➔ `assets/css/global.min.css` (Cargado en todo el sitio; incluye base, cabecera, pie, estilos del paginado shortcode y el sistema de rejilla/sidebar global).
  - `src/scss/blog.scss` ➔ `assets/css/blog.min.css` (Índice de blog y categorías).
  - `src/scss/single.scss` ➔ `assets/css/single.min.css` (Detalle de entradas de blog).
  - `src/scss/home.scss` ➔ `assets/css/home.min.css` (Página de inicio).
- **Regla de Rejillas y Sidebar Globales:** Nunca limites los estilos del diseño de la rejilla (`.de-blog-grid-layout`), el lateral del blog (`.de-sidebar`) o el paginado (`.de-blog-pagination`) en hojas específicas de páginas (como `blog.css` o `single.css`). Debes centralizarlos en parciales globales de SASS (ej. `src/scss/global/_sidebar.scss` y `src/scss/global/_pagination.scss`) e importarlos en `global.scss` para asegurar su carga consistente en cualquier plantilla (`single.html`, `archive.html`, `home.html`, etc.).
- **Clases CSS Limpias:** Evitar clases innecesarias o redundantes. Priorizar la simplicidad y el uso de la estructura BEM simplificada.

### Restricciones

- No refactorizar el PHP del theme a **programación orientada a objetos**.
- No sugerir **frameworks modernos** para el front del theme.
- No **abstraer** lógica si no es estrictamente necesario.

---

## Conversión de componentes visuales

### Convertir a pattern

Usar para: hero, features, FAQ, CTA, pricing, testimonios, logos, cards de servicios, tech stack, etc.

**Ubicación:** `/patterns/`

### Convertir a template part

Usar para: header, footer, sidebar, avisos globales.

**Ubicación:** `/parts/`

### Cuándo delegar al plugin de bloques

Solo cuando se necesite, por ejemplo: tabs dinámicos, sliders avanzados, AJAX, APIs, filtros dinámicos, calculadoras, inspector complejo. **No** implementar eso como PHP/JS suelto dentro del theme: usar **`devengine-pro-blocks`** y las reglas del plugin.

---

## Reglas del theme FSE

Estructura mínima:

```txt
devengine-pro/
style.css
theme.json
functions.php
templates/
parts/
patterns/
assets/
```

### Archivos esperados

- `templates/front-page.html`
- `templates/index.html`
- `parts/header.html`
- `parts/footer.html`

### Regla general de composición de plantillas (obligatoria)

- Cualquier archivo en `templates/*.html` debe actuar como **orquestador mínimo**: ensamblar `template parts` y `patterns`, sin maquetación extensa inline.
- Cada sección visual editable a futuro (hero, grids, CTA, FAQs, listados, etc.) debe implementarse como **pattern independiente** dentro de `patterns/`.
- Evitar duplicar secciones dentro de varias plantillas; reutilizar patrones para mantener consistencia visual y reducir regresiones.
- Priorizar bloques nativos de WordPress dentro de patterns para asegurar edición visual en Site Editor.
- Si el editor muestra bloques inválidos o markup roto, simplificar la plantilla a composición por patterns antes de ajustar estilos finos.
- Mantener la paridad visual en `theme.json` + hojas de estilo compiladas en `assets/css/` (gestionadas mediante SASS); no usar las plantillas como lugar principal de estilos o estructura detallada.
- Regla práctica: **una sección = un patrón reutilizable**.

### theme.json

Definir siempre: colores globales, tipografías, espaciados, anchuras de contenido (`contentSize` / `wideSize`), estilos base de bloques.

### CSS, SASS y assets

- Centralizar los archivos fuente en `src/scss/` y compilar los archivos minificados finales en `assets/css/` usando Gulp.
- **No** modificar directamente los archivos `.css` generados en `assets/css/` manualmente; cualquier edición de estilos se debe realizar en su correspondiente archivo `.scss` en `src/scss/`.
- Usar nombres consistentes (BEM o utilidades coherentes con el proyecto).
- Evitar CSS inline innesecario.

---

## Paridad visual, espaciado y cabecera (obligatorio revisar)

Al implementar o modificar secciones tomadas de un diseño (p. ej. Lovable / Figma), comprobar siempre:

### Layout y anchuras

- **`theme.json`:** separar `contentSize` (lectura, ~736px) de `wideSize` (contenedor principal, ~1152–1200px). No igualar ambos salvo decisión explícita.
- **Patterns:** el ancho “tipo `max-w-6xl`” se logra con **`align: wide`**, no solo con `layout: constrained`.
- Bloques de texto estrechos (hero, `max-w-3xl`) en grupo interno con **`max-width` en CSS** (p. ej. `de-hero-copy`) o `contentSize` acotado.

### Espaciado vertical entre secciones

- Entre bloques grandes (hero → stats → servicios → …) usar **presets** en escalas **6rem / 8rem / 10rem** (`spacing` 100 / 110 / 120) alineados al diseño fuente.
- Evitar márgenes “pegados” entre hero y la siguiente sección si el mockup muestra mucho aire.

### Cabecera flotante + glass (blur)

- Barra flotante: **`position: fixed`**, **`backdrop-filter`** + fondo semitransparente, **radio** y **max-width** alineada a `wideSize`.
- Compensar solape con **padding-top** en el hero y opcional **`scroll-padding-top`** en `html`.
- Con **admin bar:** desplazar la cabecera (`top: 32px` / `46px` en móvil).

### Coherencia de color de fondo

- Color de página del diseño en **`theme.json` → palette `background`** y repetir en secciones full-bleed si debe coincidir exactamente.